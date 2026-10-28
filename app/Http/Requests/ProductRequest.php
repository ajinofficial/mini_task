<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    protected $errorBag = 'product';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:120'],
            'code'              => ['required', 'string', 'max:40', Rule::unique('products', 'code')->ignore($this->route('product'))],
            'price'             => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'tax_percentage'    => ['required', 'numeric', 'min:0', 'max:100'],
            'stock'             => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
    }
}
