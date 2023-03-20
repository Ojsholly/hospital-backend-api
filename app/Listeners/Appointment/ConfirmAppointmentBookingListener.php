<?php

namespace App\Listeners\Appointment;

use App\Enums\AppointmentStatusEnum;
use App\Events\Appointment\PaymentConfirmedEvent;
use App\Services\Appointment\AppointmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;

class ConfirmAppointmentBookingListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(private AppointmentService $appointmentService)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentConfirmedEvent $event): void
    {
        try {
            $appointment = data_get($event, 'appointment');

            $this->appointmentService->updateAppointment($appointment->id, ['status' => AppointmentStatusEnum::CONFIRMED]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function failed(PaymentConfirmedEvent $event, Throwable $exception): void
    {
        report($exception);
    }
}
