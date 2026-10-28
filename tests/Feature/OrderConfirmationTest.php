<?php

namespace Tests\Feature;

use App\Jobs\SendOrderConfirmation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderConfirmationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_web_and_api_orders_each_queue_one_confirmation(): void
    {
        Queue::fake();
        $this->seed();
        $customer = Customer::firstOrFail();
        $product = Product::firstOrFail();
        $payload = ['customer_id' => $customer->id, 'items' => [['product_id' => $product->id, 'quantity' => 1]]];

        $this->postJson('/orders?tab=orders', $payload)->assertCreated();
        $this->postJson('/api/orders', $payload)->assertCreated();

        Queue::assertPushed(SendOrderConfirmation::class, 2);
        foreach (Order::all() as $order) {
            Queue::assertPushed(SendOrderConfirmation::class, fn ($job) => $job->orderId === $order->id && $job->afterCommit);
        }
    }

    public function test_invalid_order_does_not_queue_confirmation(): void
    {
        Queue::fake();
        $this->seed();
        $this->postJson('/orders', [])->assertUnprocessable();
        $product = Product::firstOrFail();
        $this->postJson('/api/orders', [
            'customer_id' => Customer::firstOrFail()->id,
            'items' => [['product_id' => $product->id, 'quantity' => $product->stock + 1]],
        ])->assertUnprocessable();
        Queue::assertNothingPushed();
    }

    public function test_database_job_is_processed_by_worker_and_logs_confirmation(): void
    {
        config(['queue.default' => 'database']);
        $this->seed();
        $customer = Customer::firstOrFail();
        $this->postJson('/api/orders', [
            'customer_id' => $customer->id,
            'items' => [['product_id' => Product::firstOrFail()->id, 'quantity' => 1]],
        ])->assertCreated();
        $order = Order::firstOrFail();
        $this->assertDatabaseCount('jobs', 1);
        Log::shouldReceive('info')->once()->with('Order confirmation email simulated.', \Mockery::on(
            fn ($context) => $context['order_id'] === $order->id && $context['to'] === $customer->email && $context['currency'] === 'INR'
        ));
        $this->artisan('queue:work', ['connection' => 'database', '--once' => true, '--tries' => 1])->assertExitCode(0);
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 0);
    }

    public function test_confirmation_is_not_queued_until_commit_and_is_discarded_on_rollback(): void
    {
        config(['queue.default' => 'database']);
        DB::beginTransaction();
        SendOrderConfirmation::dispatch(123);
        $this->assertDatabaseCount('jobs', 0);
        DB::rollBack();
        $this->assertDatabaseCount('jobs', 0);
    }
}
