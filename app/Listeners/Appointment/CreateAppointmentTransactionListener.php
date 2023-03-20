<?php

namespace App\Listeners\Appointment;

use App\Events\Appointment\PaymentConfirmedEvent;
use App\Services\Transaction\TransactionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;

class CreateAppointmentTransactionListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentConfirmedEvent $event): void
    {
        try {
            $appointment = data_get($event, 'appointment');

            $this->transactionService->createTransaction([
                'reference' => data_get($event, 'reference'),
                'user_id' => $appointment->user_id,
                'appointment_id' => $appointment->id,
                'description' => "Payment for appointment: {$appointment->reference}",
                'type' => 'debit',
                'status' => 'success',
                'gateway' => data_get($event, 'gateway'),
                'amount' => $appointment->price,
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function failed(PaymentConfirmedEvent $event, Throwable $exception): void
    {
        report($exception);
    }
}
