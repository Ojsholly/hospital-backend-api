<?php

namespace App\Providers;

use App\Interfaces\AppointmentInterface;
use App\Interfaces\AuthInterface;
use App\Interfaces\DoctorInterface;
use App\Interfaces\FlutterwaveInterface;
use App\Interfaces\PaystackInterface;
use App\Interfaces\TransactionInterface;
use App\Interfaces\WalletInterface;
use App\Services\Appointment\AppointmentService;
use App\Services\Auth\AuthService;
use App\Services\Doctor\DoctorService;
use App\Services\PaymentGateway\FlutterwaveService;
use App\Services\PaymentGateway\PaystackService;
use App\Services\Transaction\TransactionService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AuthInterface::class, AuthService::class);
        $this->app->bind(DoctorService::class, DoctorInterface::class);
        $this->app->bind(WalletService::class, WalletInterface::class);
        $this->app->bind(AppointmentService::class, AppointmentInterface::class);
        $this->app->bind(TransactionService::class, TransactionInterface::class);
        $this->app->bind(PaystackService::class, PaystackInterface::class);
        $this->app->bind(FlutterwaveService::class, FlutterwaveInterface::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
