<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerHistoryRequest;
use App\Http\Requests\LowStockRequest;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class StoreApiController extends Controller
{
    public function customerOrders(CustomerHistoryRequest $request): JsonResponse
    {
        $customer = Customer::where('email', $request->validated('email'))->firstOrFail();

        return response()->json(array(
            'customer' => $customer,
            'data' => $customer->orders()->with('items.product')->latest()->get(),
        ));
    }

    public function lowStock(LowStockRequest $request): JsonResponse
    {
        $threshold = (int) $request->validated('threshold', config('store.low_stock_threshold', 5));

        return response()->json(array(
            'threshold' => $threshold,
            'data' => Product::where('stock', '<=', $threshold)->orderBy('stock')->orderBy('id')->get(),
        ));
    }
}
