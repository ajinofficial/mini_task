<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LowStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['threshold' => ['sometimes', 'required', 'integer', 'min:0', 'max:1000000']];
    }
}
