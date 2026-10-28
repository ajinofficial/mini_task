<?php

use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'index'])->name('store.home');

foreach (['overview', 'orders', 'products', 'customers'] as $page) {
    Route::get('/'.$page, [StoreController::class, 'index'])
        ->defaults('page', $page)
        ->name($page.'.index');
}

Route::post('/products', [StoreController::class, 'createProduct'])->name('products.store');
Route::put('/products/{product}', [StoreController::class, 'updateProduct'])->name('products.update');
Route::post('/customers', [StoreController::class, 'createCustomer'])->name('customers.store');
Route::post('/orders', [StoreController::class, 'createOrder'])->name('orders.store');
Route::get('/orders/{order}/bill', [StoreController::class, 'bill'])->name('orders.bill');
Route::post('/orders/{order}/payments', [StoreController::class, 'payBalance'])->name('orders.payments.store');
