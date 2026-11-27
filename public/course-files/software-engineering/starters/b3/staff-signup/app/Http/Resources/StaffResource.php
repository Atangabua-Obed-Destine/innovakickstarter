<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// What the API shows about a member of staff. Never the password, not even the hash.
class StaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'branch' => $this->branch_code,
            'is_active' => $this->is_active,
        ];
    }
}
