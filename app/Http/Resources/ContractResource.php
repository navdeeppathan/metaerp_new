<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'orderCode' => $this->order_code,
            'type' => $this->type,
            'status' => $this->status ?? 'ACTIVE',
            'deliveryTerms' => $this->delivery_terms,
            'modeOfPayment' => $this->mode_of_payment,
            'dueDate' => $this->due_date?->toISOString(),
            'loadingDate' => $this->loading_date?->toISOString(),
            'stage' => $this->whenLoaded('stage'),
            'purchase' => $this->whenLoaded('purchase'),
            'sale' => $this->whenLoaded('sale'),
            'packingLists' => $this->whenLoaded('packingLists'),
            'checklists' => $this->whenLoaded('checklists'),
            'comments' => $this->whenLoaded('comments'),
        ];
    }
}
