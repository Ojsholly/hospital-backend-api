<?php

namespace App\Listeners\Appointment;

use App\Events\Appointment\AppointmentCompletedEvent;
use App\Mail\Appointment\AppointmentCompletedMail;
use App\Mail\Appointment\Doctor\NewAppointmentBookingCompletedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendAppointmentCompletionEmailListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(AppointmentCompletedEvent $event): void
    {
        $appointment = $event->appointment;

        $appointment->load('doctor', 'user');

        Mail::to($appointment->user->email)->send(new AppointmentCompletedMail($appointment));

        Mail::to($appointment->doctor->user->email)->send(new NewAppointmentBookingCompletedMail($appointment));
    }
}
