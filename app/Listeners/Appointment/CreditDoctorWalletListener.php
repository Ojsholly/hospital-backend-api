<?php

namespace App\Listeners\Appointment;

use App\Events\Appointment\AppointmentCompletedEvent;
use App\Services\Transaction\TransactionService;
use App\Services\Wallet\WalletService;
use App\Traits\ReferenceTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class CreditDoctorWalletListener implements ShouldQueue
{
    use ReferenceTrait;

    /**
     * Create the event listener.
     */
    public function __construct(private WalletService $walletService, private TransactionService $transactionService)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(AppointmentCompletedEvent $event): void
    {
        DB::transaction(function () use ($event) {
            $appointment = $event->appointment;

            $pay = $appointment->price - config('settings.service-charge');

            $appointment->load('doctor');

            $this->walletService->creditDoctorWallet($appointment->doctor_id, $pay);

            $this->transactionService->createTransaction([
                'appointment_id' => $appointment->id,
                'doctor_id' => $appointment->doctor_id,
                'amount' => $pay,
                'type' => 'credit',
                'status' => 'success',
                'reference' => $this->transactionReference(),
            ]);
        });
    }
}
