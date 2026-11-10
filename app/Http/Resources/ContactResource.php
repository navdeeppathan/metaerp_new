<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'codeMeta' => $this->code_meta,
            'vat' => $this->vat,
            'registration' => $this->registration,
            'currency' => $this->currency,
            'active' => (bool) $this->active,
            'country' => $this->whenLoaded('country'),
            'company' => $this->whenLoaded('company'),
            'addresses' => $this->whenLoaded('addresses'),
            'representatives' => $this->whenLoaded('representatives'),
            'products' => $this->whenLoaded('products'),
            'types' => $this->whenLoaded('types'),
        ];
    }
}
