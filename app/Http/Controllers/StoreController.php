<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(Request $request): View
    {
        $orders    = Order::with('customer', 'items')->latest()->get();
        $customers = Customer::with('orders')->withCount('orders')->orderBy('name')->get();

        return view('store', array(
            'products'  => Product::orderBy('name')->get(),
            'customers' => $customers,
            'orders'    => $orders,
            'metrics'   => array(
                'sales'      => Order::sum('total'),
                'orderCount' => Order::count(),
                'products'   => Product::count(),
                'lowStock'   => Product::where('stock', '<=', 5)->count(),
            ),
            'activeTab' => $request->query('tab', 'overview'),
        ));
    }

    public function createProduct(Request $request): RedirectResponse
    {
        $data = $request->validate(array(
            'name'              => ['required', 'string', 'max:120'],
            'code'              => ['required', 'string', 'max:40', 'unique:products,code'],
            'price'             => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'tax_percentage'    => ['required', 'numeric', 'min:0', 'max:100'],
            'stock'             => ['required', 'integer', 'min:0', 'max:1000000'],
        ));

        Product::create($data);

        return redirect('/?tab=products')->with('success', 'Product added to your catalog.');
    }

    public function createCustomer(Request $request): RedirectResponse
    {
        $data = $request->validate(array(
            'name'  => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:customers,email'],
        ));

        Customer::create($data);

        return redirect('/?tab=customers')->with('success', 'Customer added to your directory.');
    }

    public function createOrder(Request $request): RedirectResponse
    {
        $request->merge(['items' => collect($request->input('items', []))->filter(fn ($line) => ! empty($line['product_id']))->values()->all()]);

        $validated = $request->validate(array(
            'customer_id'           => ['required', 'exists:customers,id'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1', 'max:1000000'],
        ));

        $order = $this->saveOrder((int) $validated['customer_id'], $validated['items']);

        return redirect('/?tab=orders')->with('success', 'Order #'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT).' created and stock updated.');
    }

    public function apiCreateOrder(Request $request): JsonResponse
    {
        $request->merge(['items' => collect($request->input('items', []))->filter(fn ($line) => ! empty($line['product_id']))->values()->all()]);

        $validated = $request->validate(array(
            'customer_id'           => ['required', 'exists:customers,id'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1', 'max:1000000'],
        ));

        $order = $this->saveOrder((int) $validated['customer_id'], $validated['items']);

        $order->load(['customer', 'items.product']);

        return response()->json(['message' => 'Order created successfully.', 'data' => $order], 201);
    }

    private function saveOrder(int $customerId, array $lines): Order
    {
        return DB::transaction(function () use ($customerId, $lines) {
            $products = Product::whereIn('id', collect($lines)->pluck('product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $subtotal = 0;
            $taxTotal = 0;
            $prepared = [];

            foreach($lines as $line) 
            {
                $product  = $products->get((int) $line['product_id']);
                $quantity = (int) $line['quantity'];

                if(!$product || $quantity > $product->stock) 
                {
                    throw ValidationException::withMessages(['items' => 'One or more quantities exceed the available stock.']);
                }

                $lineSubtotal = round((float) $product->price * $quantity, 2);
                $lineTax      = round($lineSubtotal * (float) $product->tax_percentage / 100, 2);
                $subtotal    += $lineSubtotal;
                $taxTotal    += $lineTax;
                $prepared[]   = compact('product', 'quantity', 'lineSubtotal', 'lineTax');
            }

            $order = Order::create(['customer_id' => $customerId, 'subtotal' => $subtotal, 'tax' => $taxTotal, 'total' => $subtotal + $taxTotal]);

            foreach($prepared as $line) 
            {
                $product = $line['product'];

                OrderItem::create([
                    'order_id'          => $order->id,
                    'product_id'        => $product->id,
                    'quantity'          => $line['quantity'],
                    'unit_price'        => $product->price,
                    'tax_percentage'    => $product->tax_percentage,
                    'subtotal'          => $line['lineSubtotal'],
                    'tax'               => $line['lineTax'],
                    'total'             => $line['lineSubtotal'] + $line['lineTax'],
                ]);
                
                $product->decrement('stock', $line['quantity']);
            }

            return $order;
        });
    }
}
