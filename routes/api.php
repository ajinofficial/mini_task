<?php

use App\Http\Controllers\StoreApiController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::post('/orders', [StoreController::class, 'apiCreateOrder'])->name('api.orders.store');
Route::get('/customers/orders', [StoreApiController::class, 'customerOrders'])->name('api.customers.orders');
Route::get('/products/low-stock', [StoreApiController::class, 'lowStock'])->name('api.products.low-stock');
