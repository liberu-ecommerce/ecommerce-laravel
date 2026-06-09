<?php

namespace Tests\Unit;

use App\Services\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_can_be_resolved(): void
    {
        $service = app(RecommendationService::class);

        $this->assertInstanceOf(RecommendationService::class, $service);
    }
}
