<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'vat' => 'nullable|string|max:255',
            'registration' => 'nullable|string|max:255',
            'country_id' => 'nullable|integer',
            'company_id' => 'nullable|integer',
            'currency' => 'nullable|string|max:10',
        ];
    }
}
