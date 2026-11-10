<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string|in:BUY,SELL,BUY_AND_SELL,Purchase,Sales,Purchase & Sales',
            'meta_company_id' => 'nullable|integer',
            'contact_id' => 'nullable|integer',
            'product_id' => 'nullable|integer',
            'quantity' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'currency_id' => 'nullable|integer',
            'delivery_terms' => 'nullable|string|max:255',
            'mode_of_payment' => 'nullable|string|max:255',
        ];
    }
}
