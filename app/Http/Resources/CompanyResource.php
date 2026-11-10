<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'vat' => $this->vat,
            'companyNumber' => $this->company_number,
            'phoneNumber' => $this->phone_number,
            'email' => $this->email,
            'website' => $this->website,
            'active' => (bool) $this->active,
            'country' => $this->whenLoaded('country'),
            'bankAccounts' => $this->whenLoaded('bankAccounts'),
            'representatives' => $this->whenLoaded('representatives'),
        ];
    }
}
