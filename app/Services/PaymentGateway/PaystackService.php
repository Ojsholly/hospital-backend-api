<?php

namespace App\Services\PaymentGateway;

use App\Interfaces\PaystackInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class PaystackService implements PaystackInterface
{
    public function client(): PendingRequest
    {
        return Http::withToken(config('services.paystack.secret'))->acceptJson()->timeout(30)->retry(5, 100);
    }

    public function getBaseUrl(): string
    {
        return config('services.paystack.base_url');
    }

    /**
     * @throws RequestException
     */
    public function initializeTransaction(array $data): ?object
    {
        $data += [
            'channels' => ['bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer', 'eft', 'card'],
            'bearer' => 'account',
        ];

        return $this->client()->post($this->getBaseUrl().'transaction/initialize', $data)->throw()->object();
    }

    /**
     * @throws RequestException
     */
    public function verifyTransaction(string $reference): object
    {
        return $this->client()->get($this->getBaseUrl().'transaction/verify/'.$reference)->throw()->object();
    }
}
