<?php

namespace App\Interfaces;

use App\Models\Wallet;

interface WalletInterface
{
    public function createDoctorWallet(string $doctorId): Wallet;
}
