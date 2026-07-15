<?php

namespace Tests\Feature;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Registration is the only place a password enters the system unhashed, and these
 * accounts hold order history and saved payment methods. The rule itself lives in
 * AppServiceProvider::boot() via Password::defaults(), so it also covers password
 * reset and password update — every caller of PasswordValidationRules::passwordRules().
 */
class PasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The rule only adds uncompromised() (a HaveIBeenPwned lookup) in production, so
        // validating here must never leave the box. This fails loudly if that env gating
        // ever regresses, rather than quietly making the suite depend on the network.
        Http::preventStrayRequests();
    }

    public static function weakPasswords(): array
    {
        return [
            'too short' => ['Sh0rt!Aa'],            // 8 chars: passes stock Password::default()
            'no uppercase' => ['nouppercase1!x'],
            'no number' => ['NoNumbersHere!x'],
            'no symbol' => ['NoSymbolsHere1x'],
        ];
    }

    #[Test]
    #[DataProvider('weakPasswords')]
    public function registration_rejects_a_weak_password(string $password): void
    {
        $this->expectException(ValidationException::class);

        (new CreateNewUser)->create([
            'name' => 'Weak Password',
            'email' => 'weak@example.com',
            'password' => $password,
            'password_confirmation' => $password,
        ]);
    }

    #[Test]
    public function registration_accepts_a_strong_password(): void
    {
        $user = (new CreateNewUser)->create([
            'name' => 'Strong Password',
            'email' => 'strong@example.com',
            'password' => 'C0rrect-Horse-Battery!',
            'password_confirmation' => 'C0rrect-Horse-Battery!',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', ['email' => 'strong@example.com']);
    }
}
