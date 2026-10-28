<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    protected $errorBag = 'default';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id'           => ['required', 'exists:customers,id'],
            'paid_amount'           => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1', 'max:1000000'],
        ];
    }
}
