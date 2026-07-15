<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_response_carries_security_headers(): void
    {
        $response = $this->get('/');

        // Assert the page actually rendered: without this the header assertions
        // below would pass just as happily against a 500 error page.
        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNotEmpty(
            $response->headers->get('Content-Security-Policy-Report-Only'),
            'Expected a Content-Security-Policy-Report-Only header.'
        );
    }

    public function test_hsts_is_not_sent_over_plain_http(): void
    {
        $this->get('/')->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_is_sent_over_https(): void
    {
        $response = $this->get('https://localhost/');

        $this->assertStringContainsString(
            'max-age=',
            (string) $response->headers->get('Strict-Transport-Security')
        );
    }
}
