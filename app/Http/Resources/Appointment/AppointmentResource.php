<?php

namespace App\Http\Resources\Appointment;

use App\Http\Resources\Doctor\DoctorResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
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
            'reference' => $this->reference,
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'doctor' => $this->whenLoaded('doctor', fn () => new DoctorResource($this->doctor)),
            'datetime' => $this->datetime->toDayDateTimeString(),
            'reason' => $this->reason,
            'symptoms' => $this->symptoms,
            'diagnosis' => $this->diagnosis,
            'prescription' => $this->prescription,
            'comment' => $this->comment,
            'address' => $this->address,
            'price' => number_format($this->price, 2),
            'status' => $this->status,
            'created_at' => $this->created_at->toDayDateTimeString(),
            'updated_at' => $this->updated_at->diffForHumans(),
        ];
    }
}
