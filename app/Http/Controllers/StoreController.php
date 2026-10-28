<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiOrderRequest;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\ProductRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request): View
    {
        $page = $request->route('page') ?? $request->query('tab', 'overview');
        $page = in_array($page, ['overview', 'orders', 'products', 'customers'], true) ? $page : 'overview';

        $orders     = Order::with('customer', 'items')->latest()->get();
        $customers  = Customer::with('orders')->withCount('orders')->orderBy('name')->get();
        $points     = [];

        foreach([36, 52, 43, 66, 55, 73, 60, 84, 69, 92, 76, 104] as $i => $value) 
        {
            $points[] = (22 + $i * 69).','.(151 - $value);
        }

        $chartLine    = implode(' ', $points);

        return view('store.'.$page, [
            'icons'         => ['HME-001' => '☕', 'BAG-014' => '◫', 'HME-026' => '◉', 'KIT-008' => '◍', 'KIT-021' => '▤', 'DEC-003' => '♢'],
            'avatars'       => ['avatar', 'blue', 'rose', 'violet'],
            'recentOrders'  => $orders,
            'chartLine'     => $chartLine,
            'chartArea'     => '22,151 '.$chartLine.' 781,151',
            'products'      => Product::orderBy('name')->get(),
            'customers'     => $customers,
            'orders'        => $orders,
            'metrics'       => [
                'sales'         => Order::sum('total'),
                'orderCount'    => Order::count(),
                'products'      => Product::count(),
                'lowStock'      => Product::where('stock', '<=', 5)->count(),
            ],
            'activeTab'     => $page,
        ]);
    }

    public function createProduct(ProductRequest $request): RedirectResponse|JsonResponse
    {
        $data    = $request->validated();
        $product = Product::create($data);

        if($request->expectsJson()) 
        {
            return response()->json([
                'message'   => 'Product added to your catalog.',
                'data'      => $product,
                'html'      => $this->index($request)->render(),
            ], 201);
        }

        return redirect()->route('products.index')->with('success', 'Product added to your catalog.');
    }

    public function updateProduct(ProductRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        $product->update($data);

        if($request->expectsJson()) 
        {
            return response()->json([
                'message'   => 'Product updated successfully.',
                'data'      => $product->fresh(),
                'html'      => $this->index($request)->render(),
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function createCustomer(CustomerRequest $request): RedirectResponse|JsonResponse
    {
        $data       = $request->validated();
        $customer   = Customer::create($data);

        if($request->expectsJson()) 
        {
            return response()->json([
                'message'   => 'Customer added to your directory.',
                'data'      => $customer,
                'html'      => $this->index($request)->render(),
            ], 201);
        }

        return redirect()->route('customers.index')->with('success', 'Customer added to your directory.');
    }

    public function createOrder(OrderRequest $request): RedirectResponse|JsonResponse
    {
        $validated  = $request->validated();
        $order      = $this->orders->create((int) $validated['customer_id'], $validated['items'], (float) ($validated['paid_amount'] ?? 0));

        if($request->expectsJson()) 
        {
            return response()->json([
                'message'   => 'Order #'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT).' created and stock updated.',
                'data'      => $order->load(['customer', 'items.product']),
                'products'  => Product::orderBy('name')->get(),
                'html'      => $this->index($request)->render(),
            ], 201);
        }

        return redirect()->route('orders.index')->with('success', 'Order #'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT).' created and stock updated.');
    }

    public function payBalance(PaymentRequest $request, Order $order): JsonResponse
    {
        $validated  = $request->validated();
        $order      = $this->orders->payBalance($order, $validated);

        return response()->json([
            'message'   => 'Payment recorded successfully.',
            'data'      => $order,
            'html'      => $this->index($request)->render(),
        ]);
    }

    public function apiCreateOrder(ApiOrderRequest $request): JsonResponse
    {
        $validated  = $request->validated();
        $order      = $this->orders->createFromApi($validated);

        $order->load(['customer', 'items.product']);

        return response()->json(['message' => 'Order created successfully.', 'data' => $order], 201);
    }
}
