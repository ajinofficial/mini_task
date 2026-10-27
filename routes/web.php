<?php

use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'index'])->name('store.home');
Route::post('/products', [StoreController::class, 'createProduct'])->name('products.store');
Route::post('/customers', [StoreController::class, 'createCustomer'])->name('customers.store');
Route::post('/orders', [StoreController::class, 'createOrder'])->name('orders.store');
Route::post('/api/orders', [StoreController::class, 'apiCreateOrder'])->name('api.orders.store');
