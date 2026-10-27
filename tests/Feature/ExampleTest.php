<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Customer;
use App\Models\Product;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed();

        $response = $this->get('/');

        $response->assertOk()->assertSee('Good morning, Jamie')->assertSee('Everyday Ceramic Mug');
    }

    public function test_an_order_calculates_tax_and_reduces_stock(): void
    {
        $this->seed();
        $customer = Customer::firstOrFail();
        $product = Product::where('code', 'HME-001')->firstOrFail();

        $this->postJson('/api/orders', [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])->assertCreated()
            ->assertJsonPath('data.subtotal', '36.00')
            ->assertJsonPath('data.tax', '2.97')
            ->assertJsonPath('data.total', '38.97');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 40]);
        $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_an_order_cannot_exceed_available_stock(): void
    {
        $this->seed();
        $customer = Customer::firstOrFail();
        $product = Product::where('code', 'KIT-008')->firstOrFail();

        $this->postJson('/api/orders', [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
        ])->assertUnprocessable();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
        $this->assertDatabaseCount('orders', 0);
    }
}
