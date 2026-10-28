<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Customer;
use App\Models\Product;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_ajax_order_creation_returns_updated_page_and_stock(): void
    {
        $this->seed();
        $product = Product::where('code', 'HME-001')->firstOrFail();
        $response = $this->postJson('/orders?tab=orders', [
            'customer_id' => Customer::firstOrFail()->id,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'paid_amount' => '40.00',
        ])->assertCreated()->assertJsonPath('data.total', '38.97')
            ->assertJsonPath('data.paid_amount', '40.00')
            ->assertJsonStructure(['html', 'products', 'message']);
        $this->assertStringContainsString('id="view-orders"', $response->json('html'));
        $this->assertEquals(40, collect($response->json('products'))->firstWhere('id', $product->id)['stock']);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_ajax_order_validation_returns_field_errors_without_saving(): void
    {
        $this->seed();
        $this->postJson('/orders', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['customer_id', 'items']);
        $this->postJson('/orders', [
            'customer_id' => Customer::firstOrFail()->id,
            'items' => [
                ['product_id' => '', 'quantity' => 1],
                ['product_id' => Product::firstOrFail()->id, 'quantity' => 0],
            ],
            'paid_amount' => -1,
        ])->assertUnprocessable()->assertJsonValidationErrors(['items.0.product_id', 'items.1.quantity', 'paid_amount']);
        $product = Product::where('code', 'KIT-008')->firstOrFail();
        $this->postJson('/orders', [
            'customer_id' => Customer::firstOrFail()->id,
            'items' => [['product_id' => $product->id, 'quantity' => $product->stock + 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors('items');
        $this->assertDatabaseCount('orders', 0);
        $this->assertEquals($product->stock, $product->fresh()->stock);
    }

    public function test_order_payment_is_saved_and_balance_and_change_are_calculated(): void
    {
        $this->seed();
        $customer = Customer::firstOrFail();
        $product = Product::where('code', 'HME-001')->firstOrFail();
        foreach ([0 => 19.49, 10 => 9.49, 20 => 0] as $paid => $balance) {
            $response = $this->postJson('/api/orders', [
                'customer_id' => $customer->id,
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
                'paid_amount' => $paid,
            ])->assertCreated()->assertJsonPath('data.paid_amount', number_format($paid, 2, '.', ''));
            $order = \App\Models\Order::findOrFail($response->json('data.id'));
            $this->assertEquals($balance, $order->balance_due);
            $this->assertEquals($paid === 20 ? 0.51 : 0, $order->change_due);
        }
        $this->get('/orders')->assertOk()->assertSee('Partially paid')->assertSee('Unpaid')->assertSee('Paid');
    }

    public function test_invalid_order_payments_do_not_create_orders_or_reduce_stock(): void
    {
        $this->seed();
        $customer = Customer::firstOrFail();
        $product = Product::firstOrFail();
        foreach ([-1, 'invalid', '12.345', '10000000000'] as $paid) {
            $this->postJson('/api/orders', [
                'customer_id' => $customer->id,
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
                'paid_amount' => $paid,
            ])->assertUnprocessable()->assertJsonValidationErrors('paid_amount');
        }
        $this->assertDatabaseCount('orders', 0);
        $this->assertEquals($product->stock, $product->fresh()->stock);
    }

    public function test_web_order_saves_customer_payment(): void
    {
        $this->seed();
        $this->post('/orders', [
            'customer_id' => Customer::firstOrFail()->id,
            'items' => [['product_id' => Product::firstOrFail()->id, 'quantity' => 1]],
            'paid_amount' => '50.00',
        ])->assertRedirect(route('orders.index'));
        $this->assertDatabaseHas('orders', ['paid_amount' => 50]);
    }

    public function test_ajax_customer_creation_and_validation(): void
    {
        $response = $this->postJson('/customers?tab=customers', [
            'name' => 'New Customer', 'email' => 'new@example.com',
        ])->assertCreated()->assertJsonPath('data.name', 'New Customer')
            ->assertJsonPath('message', 'Customer added to your directory.');
        $this->assertStringContainsString('new@example.com', $response->json('html'));
        $this->assertStringContainsString('id="view-customers"', $response->json('html'));
        $this->postJson('/customers', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
        $this->postJson('/customers', ['name' => 'Test', 'email' => 'invalid'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->postJson('/customers', ['name' => 'Test', 'email' => 'new@example.com'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertDatabaseCount('customers', 1);
    }

    public function test_customer_validation_preserves_input_and_reopens_correct_modal(): void
    {
        $this->from('/customers')->post('/customers', ['name' => 'Keep Name', 'email' => 'invalid'])
            ->assertRedirect('/customers')->assertSessionHasErrors('email', null, 'customer');
        $this->get('/customers')->assertOk()
            ->assertSee('value="Keep Name"', false)
            ->assertSee('class="modal-backdrop open" id="customerModal"', false)
            ->assertSee('The email field must be a valid email address.');
    }

    public function test_product_can_be_edited_with_ajax_and_keep_its_own_code(): void
    {
        $this->seed();
        $product = Product::firstOrFail();
        $this->putJson('/products/'.$product->id.'?tab=products', [
            'name' => 'Updated product', 'code' => $product->code,
            'price' => 250, 'tax_percentage' => 18, 'stock' => 15,
        ])->assertOk()->assertJsonPath('data.name', 'Updated product')
            ->assertJsonPath('data.price', '250.00')->assertJsonPath('data.stock', 15)
            ->assertJsonPath('message', 'Product updated successfully.');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated product', 'stock' => 15]);
        $this->get('/products')->assertSee('Updated product')
            ->assertSee('data-edit-product="'.$product->id.'"', false);
    }

    public function test_product_edit_rejects_invalid_values_and_another_products_code(): void
    {
        $this->seed();
        $products = Product::take(2)->get();
        $product = $products[0];
        $this->putJson('/products/'.$product->id, [
            'name' => '', 'code' => $products[1]->code,
            'price' => 0, 'tax_percentage' => 101, 'stock' => -1,
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'code', 'price', 'tax_percentage', 'stock']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => $product->name, 'code' => $product->code, 'stock' => $product->stock]);
        $this->putJson('/products/999999', [])->assertNotFound();
    }

    public function test_ajax_product_creation_returns_json_and_updated_catalog(): void
    {
        $this->postJson('/products?tab=products', [
            'name' => 'Ajax product', 'code' => 'AJAX-001',
            'price' => 12, 'tax_percentage' => 5, 'stock' => 7,
        ])->assertCreated()->assertJsonPath('data.name', 'Ajax product')
            ->assertJsonPath('message', 'Product added to your catalog.')
            ->assertJsonStructure(['html']);

        $this->assertDatabaseHas('products', ['code' => 'AJAX-001', 'stock' => 7]);
        $this->postJson('/products', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'code', 'price', 'tax_percentage', 'stock']);
        $this->postJson('/products', [
            'name' => 'Duplicate', 'code' => 'AJAX-001',
            'price' => 12, 'tax_percentage' => 5, 'stock' => 7,
        ])->assertUnprocessable()->assertJsonValidationErrors('code');
        $this->assertDatabaseCount('products', 1);
    }

    public function test_product_required_errors_are_displayed_in_the_product_modal(): void
    {
        $this->from('/products')->post('/products', [])
            ->assertRedirect('/products')
            ->assertSessionHasErrors(['name', 'code', 'price', 'tax_percentage', 'stock'], null, 'product');

        $this->get('/products')->assertOk()
            ->assertSee('The name field is required.')
            ->assertSee('The code field is required.')
            ->assertSee('The price field is required.')
            ->assertSee('The tax percentage field is required.')
            ->assertSee('The stock field is required.')
            ->assertSee('class="modal-backdrop open" id="productModal"', false);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_validation_shows_duplicate_and_range_errors_and_keeps_input(): void
    {
        $this->seed();
        $this->from('/products')->post('/products', [
            'name' => 'My new product', 'code' => 'HME-001',
            'price' => 0, 'tax_percentage' => 101, 'stock' => -1,
        ])->assertSessionHasErrors(['code', 'price', 'tax_percentage', 'stock'], null, 'product');

        $this->get('/products')->assertOk()
            ->assertSee('The code has already been taken.')
            ->assertSee('The price field must be at least 0.01.')
            ->assertSee('The tax percentage field must not be greater than 100.')
            ->assertSee('The stock field must be at least 0.')
            ->assertSee('value="My new product"', false);
    }

    public function test_sidebar_pages_have_separate_views_and_shared_navigation(): void
    {
        $this->seed();

        foreach (['overview', 'orders', 'products', 'customers'] as $page) {
            $response = $this->get('/'.$page);
            $response->assertOk()->assertViewIs('store.'.$page)
                ->assertSee('id="view-'.$page.'"', false);

            foreach (['overview', 'orders', 'products', 'customers'] as $link) {
                $response->assertSee(route($link.'.index'));
                if ($link !== $page) {
                    $response->assertDontSee('id="view-'.$link.'"', false);
                }
            }
        }
    }

    public function test_product_and_customer_forms_redirect_to_their_pages(): void
    {
        $this->post('/products', [
            'name' => 'Test product', 'code' => 'TEST-001',
            'price' => 10, 'tax_percentage' => 5, 'stock' => 3,
        ])->assertRedirect(route('products.index'));

        $this->post('/customers', [
            'name' => 'Test Customer', 'email' => 'customer@example.com',
        ])->assertRedirect(route('customers.index'));

        $this->get('/products')->assertSee('Test product');
        $this->get('/customers')->assertSee('customer@example.com');
    }

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
