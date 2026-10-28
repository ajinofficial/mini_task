<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmation implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $orderId)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $order = Order::with('customer')->findOrFail($this->orderId);

        Log::info('Order confirmation email simulated.', [
            'order_id'      => $order->id,
            'to'            => $order->customer->email,
            'customer_name' => $order->customer->name,
            'subject'       => 'Order #'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT).' confirmation',
            'total'         => $order->total,
            'currency'      => 'INR',
        ]);
    }
}
