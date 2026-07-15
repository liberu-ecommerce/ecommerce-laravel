<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConnectedAccountsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Socialstream's ConnectedAccountsForm reads `auth()->user()->connectedAccounts`
     * (vendor/bursteri/socialstream/src/Http/Livewire/ConnectedAccountsForm.php:118).
     * Without HasConnectedAccounts on the User model that relation does not exist, so
     * the property is null and ->map() fatals — a 500 on /user/profile the moment a
     * merchant enables any social provider.
     */
    public function test_profile_page_renders_when_a_social_provider_is_enabled(): void
    {
        config(['socialstream.providers' => ['google']]);

        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($user)->get('/user/profile')->assertOk();
    }
}
