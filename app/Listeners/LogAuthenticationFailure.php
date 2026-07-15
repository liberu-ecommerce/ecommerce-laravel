<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Log;

/**
 * Records failed logins and throttle lockouts.
 *
 * Fortify throttles login at 5/min, but nothing was recording the attempts, so
 * credential stuffing left no trace at all (OWASP A09). Without this, the first
 * evidence of an attack is a support ticket.
 */
class LogAuthenticationFailure
{
    public function handleFailed(Failed $event): void
    {
        // Whitelist the email explicitly. $event->credentials also holds the
        // submitted *password*, so this must never log the array wholesale.
        Log::warning('Failed login attempt', [
            'email' => $event->credentials['email'] ?? null,
            'ip' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 200),
            // Separates "wrong password on a real account" (targeted) from
            // "probing an address that doesn't exist" (spray).
            'account_exists' => $event->user !== null,
            'guard' => $event->guard,
        ]);
    }

    public function handleLockout(Lockout $event): void
    {
        Log::warning('Login throttled after repeated failures', [
            'email' => $event->request->input('email'),
            'ip' => $event->request->ip(),
        ]);
    }
}
