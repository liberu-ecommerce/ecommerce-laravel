<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fortify throttles login at 5/min, but nothing recorded the attempts — so
 * credential stuffing was entirely invisible (OWASP A09). These tests pin both
 * halves of the contract: the attempt IS recorded, and the submitted password
 * is NOT, because Failed::$credentials carries it.
 */
class FailedLoginLoggingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_failed_login_is_logged_with_the_email_and_ip(): void
    {
        Log::spy();

        event(new Failed('web', null, ['email' => 'victim@example.com', 'password' => 'wrong-password']));

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(function (string $message, array $context) {
                return str_contains($message, 'Failed login')
                    && $context['email'] === 'victim@example.com'
                    && array_key_exists('ip', $context);
            });
    }

    /** The whole point: Failed::$credentials contains the password. It must never reach the log. */
    #[Test]
    public function the_submitted_password_is_never_logged(): void
    {
        Log::spy();

        event(new Failed('web', null, ['email' => 'victim@example.com', 'password' => 'Sup3rSecret!Password']));

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(function (string $message, array $context) {
                $flat = json_encode([$message, $context]);

                return ! str_contains($flat, 'Sup3rSecret!Password')
                    && ! array_key_exists('password', $context);
            });
    }

    /** Distinguishes "wrong password for a real account" from "probing a non-existent one". */
    #[Test]
    public function the_log_records_whether_the_account_exists(): void
    {
        $user = User::factory()->create(['email' => 'real@example.com']);
        Log::spy();

        event(new Failed('web', $user, ['email' => 'real@example.com', 'password' => 'wrong']));

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn (string $m, array $c) => $c['account_exists'] === true);
    }

    #[Test]
    public function a_lockout_is_logged(): void
    {
        Log::spy();

        event(new Lockout(Request::create('/login', 'POST', ['email' => 'victim@example.com'])));

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn (string $m, array $c) => str_contains($m, 'Login throttled'));
    }
}
