<?php

namespace App\Providers;

use App\Interfaces\AuthInterface;
use App\Interfaces\DoctorInterface;
use App\Services\Auth\AuthService;
use App\Services\Doctor\DoctorService;
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
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
