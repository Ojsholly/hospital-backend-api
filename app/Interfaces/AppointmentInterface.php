<?php

namespace App\Interfaces;

use App\Models\Appointment;

interface AppointmentInterface
{
    public function createAppointment(array $data): Appointment;
}
