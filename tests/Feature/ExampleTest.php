<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class ExampleTest extends TestCase
{

    use RefreshDatabase;
    /**
     * Test the root route ("/") returns a successful response.
     */
    public function test_the_root_route_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * Test the "/app" route requires authentication.
     */
    public function test_the_app_route_redirects_to_login(): void
    {
        $response = $this->get('/app');
        $response->assertStatus(in_array($response->status(), [302, 401]) ? $response->status() : 302);
        if ($response->status() === 302) {
            $this->assertStringContainsString('login', $response->headers->get('Location', ''));
        }
    }

    /**
     * Test the "/admin" route requires authentication.
     */
    public function test_the_admin_route_redirects_to_login(): void
    {
        $response = $this->get('/admin');
        $response->assertStatus(in_array($response->status(), [302, 401]) ? $response->status() : 302);
        if ($response->status() === 302) {
            $this->assertStringContainsString('login', $response->headers->get('Location', ''));
        }
    }
}
