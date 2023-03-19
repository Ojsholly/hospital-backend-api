<?php

namespace App\Interfaces;

use Illuminate\Http\Client\PendingRequest;

interface PaystackInterface
{
    public function client(): PendingRequest;

    public function getBaseUrl(): string;

    public function initializeTransaction(array $data): ?object;

    public function verifyTransaction(string $reference): object;
}
