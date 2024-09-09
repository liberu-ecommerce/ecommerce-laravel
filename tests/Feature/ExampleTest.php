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
     * Test the "/app" route redirects to login.
     */
    public function test_the_app_route_redirects_to_login(): void
    {
        $response = $this->get('/app');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Test the "/admin" route redirects to login.
     */
    public function test_the_admin_route_redirects_to_login(): void
    {
        $response = $this->get('/admin');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
