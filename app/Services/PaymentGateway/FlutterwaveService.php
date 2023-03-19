<?php

namespace App\Services\PaymentGateway;

use App\Interfaces\FlutterwaveInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class FlutterwaveService implements FlutterwaveInterface
{
    public function client(): PendingRequest
    {
        return Http::withToken(config('services.flutterwave.secret'))->acceptJson()->timeout(30)->retry(5, 100);
    }

    public function getBaseUrl(): string
    {
        return config('services.flutterwave.base_url');
    }

    /**
     * @throws RequestException
     */
    public function initializeTransaction(array $data): object
    {
        $data += [
            'customizations' => [
                'title' => 'Appointment Payment',
                'description' => 'Payment for appointment '.data_get($data, 'metadata.reference'),
                'logo' => 'https://assets.piedpiper.com/logo.png',
            ],
            'payment_options' => 'card,account,ussd, banktransfer, barter, nqr, credit',
        ];

        return $this->client()->post($this->getBaseUrl().'payments', $data)->throw()->object();
    }
}
