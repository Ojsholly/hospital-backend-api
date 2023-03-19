<?php

namespace App\Interfaces;

interface TransactionInterface
{
    public function initializeTransaction(array $data, string $paymentGateway): array;

    public function initializePaystackTransaction(array $data): array;

    public function verifyTransaction(string $reference, string $paymentGateway): array;

    public function verifyPaystackTransaction(string $reference): array;
}
