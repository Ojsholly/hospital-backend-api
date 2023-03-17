<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Role\RoleResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => Str::ucfirst($this->first_name),
            'last_name' => Str::ucfirst($this->last_name),
            'email' => $this->email,
            'phone' => $this->phone,
            'profile_picture' => $this->profile_picture,
            'roles' => new RoleResourceCollection($this->whenLoaded('roles')),
            'created_at' => $this->created_at->toDayDateTimeString(),
            'updated_at' => $this->updated_at->diffForHumans(),
        ];
    }
}
