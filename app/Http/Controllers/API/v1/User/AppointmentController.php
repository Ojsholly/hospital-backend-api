<?php

namespace App\Http\Controllers\API\v1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateAppointmentRequest;
use App\Http\Resources\Appointment\AppointmentResource;
use App\Services\Appointment\AppointmentService;
use App\Services\Doctor\DoctorService;
use App\Services\Transaction\TransactionService;
use App\Traits\ReferenceTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AppointmentController extends Controller
{
    use ReferenceTrait;

    public function __construct(private readonly DoctorService $doctorService, private readonly AppointmentService $appointmentService, private readonly TransactionService $transactionService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateAppointmentRequest $request): JsonResponse
    {
        try {
            $doctor = $this->doctorService->getDoctorById($request->doctor_id);
            $data = $request->validated() + [
                'price' => ceil($doctor->consultation_fee + config('settings.service-charge')),
                'reference' => $this->appointmentReference(),
            ];

            $appointment = $this->appointmentService->createAppointment($data);

            $paymentData = $this->transactionService->initializeTransaction($appointment->load('user')->toArray(), $request->payment_gateway);
        } catch (Throwable $exception) {
            report($exception);

            return response()->internalServerError('An error occurred while creating your appointment.');
        }

        return response()->created([
            'payment' => $paymentData,
            'appointment' => new AppointmentResource($appointment),
        ], 'Your appointment has been created successfully. You will be redirected to the payment page shortly.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
