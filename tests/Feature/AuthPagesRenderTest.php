<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Guards the auth and account surfaces against the layout components going missing.
 *
 * These pages all 500'd because <x-guest-layout> and <x-app-layout> were referenced
 * but never created. Nothing caught it: no test rendered them, and the two flows that
 * matter most — recovering an account and completing a 2FA challenge — are exactly the
 * ones nobody exercises by hand.
 *
 * The pairing was especially unkind: 2FA is enabled in Fortify, so a user who turned
 * it on would be locked out by the crashing challenge page, and the password-reset
 * escape hatch crashed too.
 */
class AuthPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string}> */
    public static function guestRoutes(): array
    {
        return [
            'login' => ['/login'],
            'register' => ['/register'],
            'forgot password' => ['/forgot-password'],
        ];
    }

    #[Test]
    #[DataProvider('guestRoutes')]
    public function guest_auth_page_renders(string $uri): void
    {
        $this->get($uri)->assertStatus(200);
    }

    /** The page reached from the emailed reset link. Previously a hard 500. */
    #[Test]
    public function password_reset_page_renders_from_an_emailed_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->get('/reset-password/'.$token.'?email='.urlencode($user->email))
            ->assertStatus(200)
            ->assertSee('name="password"', false);
    }

    /** If this 500s, anyone with 2FA enabled cannot log in at all. */
    #[Test]
    public function two_factor_challenge_page_renders(): void
    {
        $user = User::factory()->create();

        // Fortify reads the pending 2FA user out of the session.
        $this->session(['login.id' => $user->id, 'login.remember' => false]);

        $this->get('/two-factor-challenge')->assertStatus(200);
    }

    #[Test]
    public function password_confirmation_page_renders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/user/confirm-password')->assertStatus(200);
    }

    /**
     * Covers <x-app-layout>, which was missing alongside guest-layout.
     *
     * A view render rather than an HTTP call, deliberately. The only routes using
     * this layout are team pages behind a policy, and a 403 short-circuits before
     * the view is ever touched — so an HTTP assertion would pass without proving
     * the component resolves, which is the exact bug being guarded. /user/profile
     * is unusable here for a different reason: socialstream's ConnectedAccountsForm
     * calls ->map() on null, so it would test a vendor fault instead of this layout.
     */
    #[Test]
    public function the_app_layout_component_resolves_and_renders_a_document(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $this->actingAs($user);

        $html = view('teams.create', ['user' => $user])->render();

        $this->assertStringContainsString('<html', $html);
        $this->assertStringContainsString('</body>', $html);
    }

    /** The public login page must not advertise admin usernames. */
    #[Test]
    public function login_page_does_not_publish_demo_credentials(): void
    {
        $response = $this->get('/login');

        $response->assertDontSee('Demo Credentials');
        $response->assertDontSee('admin@example.com');
        $response->assertDontSee('staff@example.com');
    }

    /**
     * Social login is opt-in. Nine providers used to render unconditionally while
     * config/services.php held credentials for none of them, so every button threw
     * DriverMissingConfigurationException — a 500 — when clicked.
     *
     * Asserted as behaviour rather than by reading the default: phpunit.xml enables
     * the full provider list so the Socialstream registration suite still runs, so
     * the config default is not observable from in here. What matters to a visitor
     * is that an unconfigured install offers no button it cannot honour.
     */
    #[Test]
    public function social_login_is_absent_until_a_provider_is_configured(): void
    {
        config(['socialstream.providers' => []]);

        $this->get('/login')
            ->assertStatus(200)
            ->assertDontSee('Or log in with');
    }
}
