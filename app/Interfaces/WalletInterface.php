<?php

namespace App\Interfaces;

use App\Models\Wallet;

interface WalletInterface
{
    public function createDoctorWallet(string $doctorId): Wallet;

    public function getDoctorWallet(string $doctorId): Wallet;

    public function creditDoctorWallet(string $doctorId, float $amount): Wallet;
}
