<?php

namespace App\Services\Wallet;

use App\Interfaces\WalletInterface;
use App\Models\Wallet;

class WalletService implements WalletInterface
{
    public function createDoctorWallet(string $doctorId): Wallet
    {
        return Wallet::create([
            'doctor_id' => $doctorId,
        ]);
    }

    public function getDoctorWallet(string $doctorId): Wallet
    {
        return Wallet::where('doctor_id', $doctorId)->first();
    }

    public function creditDoctorWallet(string $doctorId, float $amount): Wallet
    {
        $wallet = $this->getDoctorWallet($doctorId);

        $wallet->increment('available_balance', $amount);
        $wallet->increment('ledger_balance', $amount);

        return $wallet;
    }
}
