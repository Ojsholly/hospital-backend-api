<?php

namespace App\Providers;

use App\Events\Appointment\PaymentConfirmedEvent;
use App\Listeners\Appointment\ConfirmAppointmentBookingListener;
use App\Listeners\Appointment\CreateAppointmentTransactionListener;
use App\Listeners\Doctor\CreateWalletListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Verified::class => [
            CreateWalletListener::class,
        ],
        PaymentConfirmedEvent::class => [
            ConfirmAppointmentBookingListener::class,
            CreateAppointmentTransactionListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
