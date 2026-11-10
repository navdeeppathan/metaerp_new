<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'displayName' => $this->displayName,
            'role' => $this->role,
            'occupation' => $this->occupation,
            'photoUrl' => $this->photo_url,
            'active' => (bool) $this->active,
            'enable2fa' => (bool) $this->enable2fa,
        ];
    }
}
