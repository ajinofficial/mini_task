<?php

namespace App\Http\Requests;

class ApiOrderRequest extends OrderRequest
{
    protected function prepareForValidation(): void
    {
        $items = $this->input('items', []);

        if(is_array($items)) 
        {
            $this->merge(['items' => collect($items)->filter(fn ($line) => ! empty($line['product_id']))->values()->all()]);
        }
    }

    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'customer_id'   => ['required_without:email', 'exists:customers,id'],
            'email'         => ['required_without:customer_id', 'email', 'max:190'],
            'name'          => ['required_without:customer_id', 'string', 'max:120'],
        ]);
    }
}
