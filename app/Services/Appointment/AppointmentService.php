<?php

namespace App\Services\Appointment;

use App\Interfaces\AppointmentInterface;
use App\Models\Appointment;

class AppointmentService implements AppointmentInterface
{
    public function createAppointment(array $data): Appointment
    {
        return Appointment::create($data);
    }
}
