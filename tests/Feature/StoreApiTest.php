<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StoreApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_api_creates_customer_by_email_and_reuses_existing_customer(): void
    {
        Queue::fake();
        $this->seed();
        $payload = ['email' => 'api@example.com', 'name' => 'API Customer', 'items' => [
            ['product_id' => Product::firstOrFail()->id, 'quantity' => 1],
        ]];
        $first = $this->postJson('/api/orders', $payload)->assertCreated()->assertJsonPath('data.customer.email', 'api@example.com');
        $payload['name'] = 'Different supplied name';
        $this->postJson('/api/orders', $payload)->assertCreated()->assertJsonPath('data.customer_id', $first->json('data.customer_id'))
            ->assertJsonPath('data.customer.name', 'API Customer');
        $this->assertEquals(1, Customer::where('email', 'api@example.com')->count());
    }

    public function test_failed_order_rolls_back_new_customer_and_validates_identity(): void
    {
        $this->seed();
        $product = Product::where('stock', 0)->firstOrFail();
        $this->postJson('/api/orders', ['name' => 'New', 'email' => 'rollback@example.com', 'items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ]])->assertUnprocessable()->assertJsonValidationErrors('items');
        $this->assertDatabaseMissing('customers', ['email' => 'rollback@example.com']);
        $this->postJson('/api/orders', ['name' => '', 'email' => 'invalid', 'items' => []])
            ->assertUnprocessable()->assertJsonValidationErrors(['email', 'name', 'items']);
    }

    public function test_customer_history_only_returns_matching_customers_orders(): void
    {
        $this->seed();
        $customers = Customer::take(2)->get();
        foreach ($customers as $customer) {
            Order::create(['customer_id' => $customer->id, 'subtotal' => 10, 'tax' => 0, 'total' => 10]);
        }
        $this->getJson('/api/customers/orders?'.http_build_query(['email' => $customers[0]->email]))
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.customer_id', $customers[0]->id);
        $this->getJson('/api/customers/orders?email=missing@example.com')->assertNotFound();
        $this->getJson('/api/customers/orders?email=invalid')->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->getJson('/api/customers/orders?'.http_build_query(['email' => Customer::skip(2)->firstOrFail()->email]))
            ->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_low_stock_threshold_is_configurable_and_includes_boundary(): void
    {
        $this->seed();
        config(['store.low_stock_threshold' => 6]);
        $this->getJson('/api/products/low-stock')->assertOk()->assertJsonPath('threshold', 6)->assertJsonCount(3, 'data');
        $this->getJson('/api/products/low-stock?threshold=0')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.stock', 0);
        $this->getJson('/api/products/low-stock?threshold=3')->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('threshold', 3);
        foreach (['-1', 'invalid', '1.5', ''] as $threshold) {
            $this->getJson('/api/products/low-stock?threshold='.$threshold)->assertUnprocessable()->assertJsonValidationErrors('threshold');
        }
    }
}
