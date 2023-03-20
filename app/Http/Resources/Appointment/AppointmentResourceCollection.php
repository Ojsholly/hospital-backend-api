<?php

namespace App\Http\Resources\Appointment;

use App\Traits\PaginationTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AppointmentResourceCollection extends ResourceCollection
{
    use PaginationTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'meta' => $this->metaData(),
            'appointments' => parent::toArray($request),
        ];
    }
}
