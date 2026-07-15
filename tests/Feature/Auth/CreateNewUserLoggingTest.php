<?php

namespace Tests\Feature\Auth;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * CreateNewUser logs its input on failure. Credentials must never reach the log.
 */
class CreateNewUserLoggingTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'S3cretPlaintext!2026';

    /**
     * The common failure path: a duplicate email. The password itself is valid,
     * so 'password_confirmation' still holds the plaintext when we hit the catch.
     */
    public function test_validation_failure_does_not_log_the_plaintext_password(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        Log::spy();

        try {
            (new CreateNewUser)->create([
                'name' => 'Test User',
                'email' => 'taken@example.com',
                'password' => self::PASSWORD,
                'password_confirmation' => self::PASSWORD,
            ]);
            $this->fail('Expected a ValidationException for the duplicate email.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
        }

        $this->assertNothingLoggedContains(self::PASSWORD);
    }

    /**
     * The QueryException path. A unique index on 'name' makes User::create fail at
     * insert time while the email uniqueness check still passes, so the bcrypt hash
     * is sitting in the exception's bindings when we reach the catch.
     */
    public function test_database_failure_does_not_log_the_password_hash(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->unique('name'));

        User::factory()->create(['name' => 'Clashing Name', 'email' => 'other@example.com']);

        Log::spy();

        try {
            (new CreateNewUser)->create([
                'name' => 'Clashing Name',
                'email' => 'fresh@example.com',
                'password' => self::PASSWORD,
                'password_confirmation' => self::PASSWORD,
            ]);
            $this->fail('Expected the insert to fail on the duplicate name.');
        } catch (Exception $e) {
            $this->assertStringContainsString('database error', strtolower($e->getMessage()));
        }

        // bcrypt hashes are generated in-flight, so match on the algorithm prefix.
        $this->assertNothingLoggedContains('$2y$');
        $this->assertNothingLoggedContains(self::PASSWORD);
    }

    /**
     * Assert Log::error() was called, and that no argument of any such call
     * contains $needle.
     */
    private function assertNothingLoggedContains(string $needle): void
    {
        Log::shouldHaveReceived('error')->withArgs(function (...$args) use ($needle) {
            $this->assertStringNotContainsString(
                $needle,
                json_encode($args, JSON_PARTIAL_OUTPUT_ON_ERROR),
                'A credential was written to the log.'
            );

            return true;
        });
    }
}
