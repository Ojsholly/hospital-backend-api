<?php

namespace App\Providers;

use App\Interfaces\AuthInterface;
use App\Interfaces\DoctorInterface;
use App\Interfaces\WalletInterface;
use App\Services\Auth\AuthService;
use App\Services\Doctor\DoctorService;
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
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
