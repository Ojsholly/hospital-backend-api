<?php

namespace App\Services\Transaction;

use App\Interfaces\TransactionInterface;
use App\Models\Transaction;
use App\Services\PaymentGateway\FlutterwaveService;
use App\Services\PaymentGateway\PaystackService;
use App\Traits\ReferenceTrait;
use Exception;
use Illuminate\Http\Client\RequestException;

class TransactionService implements TransactionInterface
{
    use ReferenceTrait;

    public function __construct(private PaystackService $paystackService, private FlutterwaveService $flutterwaveService)
    {
    }

    /**
     * @throws Exception
     */
    public function initializeTransaction(array $data, string $paymentGateway): array
    {
        return match ($paymentGateway) {
            'paystack' => $this->initializePaystackTransaction($data),
            'flutterwave' => $this->intializeFlutterwaveTransaction($data),
            default => throw new Exception('Invalid payment gateway'),
        };
    }

    /**
     * @throws RequestException
     */
    public function initializePaystackTransaction(array $data): array
    {
        $payload = [
            'amount' => ceil($data['price'] * 100),
            'email' => data_get($data, 'user.email'),
            'currency' => 'NGN',
            'metadata' => [
                'appointment_id' => $data['id'],
                'reference' => $data['reference'],
            ],
            'reference' => $this->transactionReference(),
            'callback_url' => route('appointments.verify-payment', [
                'appointment_id' => $data['id'],
                'gateway' => 'paystack',
            ]),
        ];

        $response = $this->paystackService->initializeTransaction($payload);

        return [
            'payment_url' => data_get($response, 'data.authorization_url'),
            'reference' => data_get($response, 'data.reference'),
            'access_code' => data_get($response, 'data.access_code'),
        ];
    }

    public function intializeFlutterwaveTransaction(array $data): array
    {
        $payload = [
            'tx_ref' => $this->transactionReference(),
            'amount' => $data['price'],
            'currency' => 'NGN',
            'redirect_url' => route('appointments.verify-payment', [
                'appointment_id' => $data['id'],
                'gateway' => 'flutterwave',
            ]),
            'customer' => [
                'email' => data_get($data, 'user.email'),
                'name' => data_get($data, 'user.full_name'),
                'phonenumber' => data_get($data, 'user.phone'),
            ],
            'meta' => [
                'appointment_id' => $data['id'],
                'reference' => $data['reference'],
            ],
        ];

        $response = $this->flutterwaveService->initializeTransaction($payload);

        return [
            'payment_url' => data_get($response, 'data.link'),
            'reference' => data_get($response, 'data.tx_ref'),
            'access_code' => data_get($response, 'data.flw_ref'),
        ];
    }

    /**
     * @throws RequestException
     * @throws Exception
     */
    public function verifyTransaction(string $reference, string $paymentGateway): array
    {
        return match ($paymentGateway) {
            'paystack' => $this->verifyPaystackTransaction($reference),
            'flutterwave' => $this->verifyFlutterwaveTransaction($reference),
            default => throw new Exception('Invalid payment gateway'),
        };
    }

    /**
     * @throws RequestException
     */
    public function verifyPaystackTransaction(string $reference): array
    {
        $response = $this->paystackService->verifyTransaction($reference);

        throw_if(
            data_get($response, 'data.status') != 'success',
            new Exception('Payment failed')
        );

        return [
            'status' => data_get($response, 'data.status') === 'success' ? 'success' : 'failed',
            'reference' => data_get($response, 'data.reference'),
            'amount' => data_get($response, 'data.amount') / 100,
            'gateway' => 'paystack',
            'appointment_id' => data_get($response, 'data.metadata.appointment_id'),
            'description' => data_get($response, 'data.meta.narration') ?? 'Payment for appointment '.data_get($response, 'data.metadata.reference'),
            'type' => 'debit',
        ];
    }

    public function verifyFlutterwaveTransaction(string $id): array
    {
        $response = $this->flutterwaveService->verifyTransaction($id);

        throw_if(
            data_get($response, 'data.status') === 'failed' || data_get($response, 'data.status') === 'cancelled',
            new Exception('Payment failed')
        );

        return [
            'status' => data_get($response, 'data.status') === 'successful' ? 'success' : 'failed',
            'reference' => data_get($response, 'data.id'),
            'amount' => data_get($response, 'data.amount'),
            'gateway' => 'flutterwave',
            'appointment_id' => data_get($response, 'data.meta.appointment_id'),
            'description' => data_get($response, 'data.meta.narration') ?? 'Payment for appointment '.data_get($response, 'data.meta.reference'),
            'type' => 'debit',
        ];
    }

    public function createTransaction(array $data): Transaction
    {
        return Transaction::create($data);
    }
}
