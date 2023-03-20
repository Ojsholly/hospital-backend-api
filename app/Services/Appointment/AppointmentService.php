<?php

namespace App\Services\Appointment;

use App\Interfaces\AppointmentInterface;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class AppointmentService implements AppointmentInterface
{
    public function createAppointment(array $data): Appointment
    {
        return Appointment::create($data);
    }

    /**
     * @throws Throwable
     */
    public function getAppointment(string $id, array $relations = []): Appointment
    {
        $appointment = Appointment::find($id);

        throw_if(! $appointment, new ModelNotFoundException('Appointment not found'));

        $appointment->load($relations);

        return $appointment;
    }

    /**
     * @throws Throwable
     */
    public function updateAppointment(string $id, array $data): Appointment
    {
        $appointment = $this->getAppointment($id);

        $appointment->update($data);

        return $appointment;
    }

    public function getAllAppointments(array $params = [], array $relations = [], array $pagination = []): LengthAwarePaginator
    {
        return Appointment::allAppointments()
            ->with($relations)
            ->where($params)
            ->paginate(data_get($pagination, 'per_page', 10));
    }
}
