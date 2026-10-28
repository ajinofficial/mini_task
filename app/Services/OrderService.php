<?php

namespace App\Services;

use App\Jobs\SendOrderConfirmation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function createFromApi(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $this->acquireSqliteWriteLock();
            $customerId = $data['customer_id'] ?? Customer::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name']],
            )->id;

            return $this->create((int) $customerId, $data['items'], (float) ($data['paid_amount'] ?? 0));
        }, 5);
    }

    public function create(int $customerId, array $lines, float $paidAmount = 0): Order
    {
        return DB::transaction(function () use ($customerId, $lines, $paidAmount) {
            $this->acquireSqliteWriteLock();

            $products = Product::whereIn('id', collect($lines)->pluck('product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $subtotal = 0;
            $taxTotal = 0;
            $prepared = [];

            foreach($lines as $line) 
            {
                $product  = $products->get((int) $line['product_id']);
                $quantity = (int) $line['quantity'];

                if(! $product || $quantity > $product->stock)
                {
                    throw ValidationException::withMessages(['items' => 'One or more quantities exceed the available stock.']);
                }

                $lineSubtotal   = round((float) $product->price * $quantity, 2);
                $lineTax        = round($lineSubtotal * (float) $product->tax_percentage / 100, 2);
                $subtotal      += $lineSubtotal;
                $taxTotal      += $lineTax;
                $prepared[]     = compact('product', 'quantity', 'lineSubtotal', 'lineTax');
            }

            $order = Order::create(['customer_id' => $customerId, 'subtotal' => $subtotal, 'tax' => $taxTotal, 'total' => $subtotal + $taxTotal, 'paid_amount' => $paidAmount]);

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

            SendOrderConfirmation::dispatch($order->id);

            return $order;
        }, 5);
    }

    public function payBalance(Order $order, array $validated): Order
    {
        return DB::transaction(function () use ($order, $validated) {
            $this->acquireSqliteWriteLock();

            $locked         = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $amountInPaise  = (int) round((float) $validated['amount'] * 100);
            $balanceInPaise = (int) round($locked->balance_due * 100);

            if($balanceInPaise <= 0) 
            {
                throw ValidationException::withMessages(['amount' => 'This order is already fully paid.']);
            }

            if($amountInPaise > $balanceInPaise) 
            {
                throw ValidationException::withMessages(['amount' => 'Payment cannot exceed the remaining balance of ₹'.number_format($locked->balance_due, 2).'.']);
            }

            $locked->update(['paid_amount' => ((int) round((float) $locked->paid_amount * 100) + $amountInPaise) / 100]);

            return $locked->fresh();
        }, 5);
    }

    private function acquireSqliteWriteLock(): void
    {
        if(DB::connection()->getDriverName() === 'sqlite') 
        {
            Product::whereRaw('0 = 1')->update(['stock' => DB::raw('stock')]);
        }
    }
}
