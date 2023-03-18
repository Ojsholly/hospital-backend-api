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
}
