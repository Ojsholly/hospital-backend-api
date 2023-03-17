<?php

namespace App\Providers;

use App\Interfaces\AuthInterface;
use App\Services\Auth\AuthService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AuthInterface::class, AuthService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
