<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function unpaidOrder(): Order
    {
        $this->seed();
        return Order::create([
            'customer_id' => Customer::firstOrFail()->id,
            'subtotal' => 100, 'tax' => 0, 'total' => 100, 'paid_amount' => 20,
        ]);
    }

    public function test_partial_and_full_balance_payments_update_order_without_changing_stock(): void
    {
        $order = $this->unpaidOrder();
        Queue::fake();
        $stock = Product::pluck('stock', 'id')->all();
        $url = '/orders/'.$order->id.'/payments?tab=orders';
        $this->get('/orders')->assertSee('Pay balance');
        $this->postJson($url, ['amount' => '30.25'])->assertOk()
            ->assertJsonPath('data.paid_amount', '50.25')->assertJsonPath('data.balance_due', 49.75);
        $response = $this->postJson($url, ['amount' => '49.75'])->assertOk()
            ->assertJsonPath('data.paid_amount', '100.00')->assertJsonPath('data.balance_due', 0);
        $this->assertStringContainsString('Completed', $response->json('html'));
        $this->assertStringNotContainsString('data-pay-order=', $response->json('html'));
        $this->assertSame($stock, Product::pluck('stock', 'id')->all());
        $this->assertDatabaseCount('orders', 1);
        Queue::assertNothingPushed();
        $this->postJson($url, ['amount' => 1])->assertUnprocessable()->assertJsonValidationErrors('amount');
        $this->assertEquals('100.00', $order->fresh()->paid_amount);
    }

    public function test_invalid_and_excess_payments_are_rejected_without_updates(): void
    {
        $order = $this->unpaidOrder();
        foreach ([null, '', 0, -1, 'invalid', '0.001', '80.01', '10000000000'] as $amount) {
            $this->postJson('/orders/'.$order->id.'/payments', ['amount' => $amount])
                ->assertUnprocessable()->assertJsonValidationErrors('amount');
        }
        $this->assertEquals('20.00', $order->fresh()->paid_amount);
        $this->postJson('/orders/999999/payments', ['amount' => 1])->assertNotFound();
    }
}
