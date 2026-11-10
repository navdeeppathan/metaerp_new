<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10',
            'vat' => 'nullable|string|max:255',
            'company_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'country_id' => 'nullable|integer',
        ];
    }
}
