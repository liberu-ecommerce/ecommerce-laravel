<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationFailure;
use App\Listeners\MergeGuestCartOnLogin;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
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
        Login::class => [
            MergeGuestCartOnLogin::class,
        ],
        // Nothing recorded authentication failures, so credential stuffing against
        // Fortify's 5/min throttle left no trace at all (OWASP A09).
        Failed::class => [
            LogAuthenticationFailure::class.'@handleFailed',
        ],
        Lockout::class => [
            LogAuthenticationFailure::class.'@handleLockout',
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
