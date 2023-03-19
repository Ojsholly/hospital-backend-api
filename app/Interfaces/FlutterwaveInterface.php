<?php

namespace App\Interfaces;

use Illuminate\Http\Client\PendingRequest;

interface FlutterwaveInterface
{
    public function getBaseUrl(): string;

    public function client(): PendingRequest;

    public function initializeTransaction(array $data): object;
}
