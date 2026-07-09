<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'parent_id' => $this->parent_id,
            'tenant_id' => parentId() ?? $this->id,
            'avatar' => $this->avatar,
            'roles' => $this->getRoleNames()->values(),
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
