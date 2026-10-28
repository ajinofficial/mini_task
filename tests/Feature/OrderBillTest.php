<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderBillTest extends TestCase
{
    use RefreshDatabase;

    public function test_bill_uses_saved_order_prices_and_displays_payment_and_print_option(): void
    {
        Queue::fake();
        $this->seed();
        $product = Product::where('code', 'HME-001')->firstOrFail();
        $customer = Customer::firstOrFail();
        $this->postJson('/api/orders', [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'paid_amount' => 40,
        ])->assertCreated();
        $order = Order::firstOrFail();
        $product->update(['price' => 999, 'tax_percentage' => 99]);
        $this->get(route('orders.bill', $order))->assertOk()->assertViewIs('store.bill')
            ->assertSee($customer->name)->assertSee($customer->email)
            ->assertSee('₹18.00')->assertSee('₹36.00')->assertSee('₹2.97')->assertSee('₹38.97')
            ->assertSee('₹40.00')->assertSee('₹1.03')->assertSee('Completed')
            ->assertSee('window.print()', false)->assertSee('Print bill / Save PDF')
            ->assertDontSee('₹999.00');
        $this->get('/orders')->assertSee(route('orders.bill', $order))->assertSee('Generate bill');
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_bill_reflects_subsequent_balance_payment_and_missing_order_is_404(): void
    {
        $this->seed();
        $order = Order::create(['customer_id' => Customer::firstOrFail()->id, 'subtotal' => 100, 'tax' => 0, 'total' => 100, 'paid_amount' => 20]);
        $this->get(route('orders.bill', $order))->assertOk()->assertSee('Partially paid')->assertSee('₹80.00');
        $this->postJson(route('orders.payments.store', $order), ['amount' => 80])->assertOk();
        $this->get(route('orders.bill', $order))->assertOk()->assertSee('Completed')->assertDontSee('Partially paid');
        $this->get('/orders/999999/bill')->assertNotFound();
    }
}
