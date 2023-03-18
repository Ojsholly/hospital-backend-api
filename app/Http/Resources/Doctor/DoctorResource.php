<?php

namespace App\Http\Resources\Doctor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class DoctorResource extends JsonResource
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
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'profile_picture' => $this->user->profile_picture,
            'specialty' => Str::ucfirst($this->specialty),
            'medical_license_number' => $this->medical_license_number,
            'medical_school' => Str::ucfirst($this->medical_school),
            'year_of_graduation' => $this->year_of_graduation,
            'biography' => $this->biography,
            'address' => $this->address,
            'consultation_fee' => number_format($this->consultation_fee, 2),
            'created_at' => $this->created_at->toDayDateTimeString(),
            'updated_at' => $this->updated_at->diffForHumans(),
            'deleted_at' => $this->deleted_at?->toDayDateTimeString(),
        ];
    }
}