<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fortify only wires per-action rate limiters for login + two-factor, leaving
 * forgot-password / register / reset-password unbounded (email-bomb, user
 * enumeration, automated signup). A Fortify-wide throttle now caps them.
 */
class FortifyRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/forgot-password', ['email' => 'user@example.com']);
        }

        $this->post('/forgot-password', ['email' => 'user@example.com'])->assertStatus(429);
    }

    public function test_registration_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/register', ['email' => "spam{$i}@example.com"]);
        }

        $this->post('/register', ['email' => 'spam@example.com'])->assertStatus(429);
    }
}
