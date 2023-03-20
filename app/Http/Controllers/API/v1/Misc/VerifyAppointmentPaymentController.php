<?php

namespace App\Http\Controllers\API\v1\Misc;

use App\Enums\AppointmentStatusEnum;
use App\Events\Appointment\PaymentConfirmedEvent;
use App\Http\Controllers\Controller;
use App\Services\Appointment\AppointmentService;
use App\Services\Transaction\TransactionService;
use Exception;
use Illuminate\Http\Request;
use Throwable;

class VerifyAppointmentPaymentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, TransactionService $transactionService, AppointmentService $appointmentService)
    {
        try {
            $reference = match ($request->route('gateway')) {
                'paystack' => $request->query('reference') ?? $request->query('trxref'),
                'flutterwave' => $request->query('transaction_id'),
            };

            $payment = $transactionService->verifyTransaction($reference, $request->route('gateway'));

            $appointment = $appointmentService->getAppointment(data_get($payment, 'appointment_id'), ['user']);

            $billableAmount = match ($request->route('gateway')) {
                'paystack' => $appointment->price * 100,
                'flutterwave' => $appointment->price,
            };

            throw_if(
                $billableAmount < data_get($payment, 'amount'),
                new Exception('Invalid payment amount')
            );

            PaymentConfirmedEvent::dispatchIf(
                $appointment->status === AppointmentStatusEnum::PENDING && ! $appointment->hasBeenPaidFor(),
                $appointment,
                $request->route('gateway'),
                $payment['reference']
            );
        } catch (Throwable $exception) {
            report($exception);

            return view('appointment.verify-payment', ['status' => 'error', 'message' => "An error occurred while processing your payment. {$exception->getMessage()}."]);
        }

        return view('appointment.verify-payment', ['status' => 'success', 'message' => 'Your appointment booking has been successfully processed.', 'redirect_url' => route('home')]);
    }
}
