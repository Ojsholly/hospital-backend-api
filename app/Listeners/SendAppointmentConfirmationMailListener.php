<?php

namespace App\Listeners;

use App\Events\Appointment\PaymentConfirmedEvent;
use App\Mail\Appointment\AppointmentConfirmationMail;
use App\Mail\Appointment\Doctor\NewAppointmentNotificationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendAppointmentConfirmationMailListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $appointment = data_get($event, 'appointment');
        $user = $appointment->user;

        $appointment->load('doctor');

        $doctor = $appointment->doctor;

        Mail::to($user->email)->send(new AppointmentConfirmationMail($appointment));
        Mail::to($doctor->user->email)->send(new NewAppointmentNotificationMail($appointment));
    }

    public function failed(PaymentConfirmedEvent $event, Throwable $exception): void
    {
        report($exception);
    }
}
