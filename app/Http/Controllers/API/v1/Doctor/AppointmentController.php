<?php

namespace App\Http\Controllers\API\v1\Doctor;

use App\Events\Appointment\AppointmentCompletedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\UpdateAppointmentRequest;
use App\Http\Resources\Appointment\AppointmentResource;
use App\Http\Resources\Appointment\AppointmentResourceCollection;
use App\Services\Appointment\AppointmentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentService $appointmentService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $per_page = request()->query('per_page', 10);
            $appointments = $this->appointmentService->getAllAppointments(
                ['doctor_id' => request()->route('doctor')], ['user'], compact('per_page')
            );
        } catch (Throwable $exception) {
            report($exception);

            return response()->internalServerError('An error occurred while fetching appointments');
        }

        return response()->success(new AppointmentResourceCollection($appointments), 'Appointments fetched successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $appointment = $this->appointmentService->getAppointment(request()->route('appointment'), ['user']);
        } catch (Throwable $exception) {
            if ($exception instanceof ModelNotFoundException) {
                return response()->notFound('Requested appointment not found');
            }

            report($exception);

            return response()->internalServerError('An error occurred while fetching appointment');
        }

        return response()->success(new AppointmentResource($appointment), 'Appointment fetched successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAppointmentRequest $request, string $id)
    {
        try {
            $appointment = $this->appointmentService->updateAppointment(request()->route('appointment'), $request->validated());

            AppointmentCompletedEvent::dispatchIf(
                $appointment->wasChanged('status') && $appointment->status === 'completed',
                $appointment
            );
        } catch (Throwable $exception) {
            if ($exception instanceof ModelNotFoundException) {
                return response()->notFound('Requested appointment not found');
            }

            report($exception);

            return response()->internalServerError('An error occurred while updating appointment');
        }

        return response()->success(new AppointmentResource($appointment), 'Appointment updated successfully');
    }
}
