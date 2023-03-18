<?php

namespace App\Listeners\Doctor;

use App\Enums\RoleEnum;
use App\Services\Wallet\WalletService;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateWalletListener implements ShouldQueue
{
    public function __construct(private readonly WalletService $walletService)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if ($event->user->hasRole(RoleEnum::DOCTOR)) {
            $this->walletService->createDoctorWallet($event->user->doctor->id);
        }
    }
}
