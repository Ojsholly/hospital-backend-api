<?php

namespace App\Interfaces;

use App\Models\Appointment;
use Illuminate\Pagination\LengthAwarePaginator;

interface AppointmentInterface
{
    public function createAppointment(array $data): Appointment;

    public function getAppointment(string $id, array $relations = []): Appointment;

    public function updateAppointment(string $id, array $data): Appointment;

    public function getAllAppointments(array $params = [], array $relations = [], array $pagination = []): LengthAwarePaginator;
}
