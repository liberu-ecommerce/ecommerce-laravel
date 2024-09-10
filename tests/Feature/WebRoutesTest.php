<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class WebRoutesTest extends TestCase
{
    use WithoutMiddleware;
    use RefreshDatabase;

    public function testHomePageRoute()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function testGuestCheckoutRoute()
    {
        $response = $this->post('/checkout/guest');
        $response->assertStatus(200);
    }

    public function testCheckoutSummaryRoute()
    {
        $response = $this->get('/checkout/summary');
        $response->assertStatus(200);
    }
}
