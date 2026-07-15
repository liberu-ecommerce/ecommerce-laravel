<?php

namespace Tests\Unit;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FortifyActionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Deliberately removed: makeTeam().
     *
     * It created a team and a `staff` role, which is precisely why this suite was
     * green while registration threw on every real request. CreateNewUser attached
     * every registrant to Team::first() and assigned `staff`; the fixture supplied
     * both, so neither the missing role nor the null team could ever surface here.
     *
     * Registration now creates a user and nothing else, so there is nothing to
     * arrange. AppPanelAccessTest asserts that directly — no team, no roles.
     */
    public function test_create_new_user_creates_user(): void
    {
        $action = new CreateNewUser;
        $user = $action->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'J0hn-Doe-Passw0rd!',
            'password_confirmation' => 'J0hn-Doe-Passw0rd!',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_create_new_user_hashes_password(): void
    {
        $action = new CreateNewUser;
        $user = $action->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'Jane-S3cure-Pass!',
            'password_confirmation' => 'Jane-S3cure-Pass!',
        ]);

        $this->assertNotEquals('Jane-S3cure-Pass!', $user->password);
        $this->assertTrue(Hash::check('Jane-S3cure-Pass!', $user->password));
    }

    public function test_create_new_user_validates_required_fields(): void
    {
        $action = new CreateNewUser;

        $this->expectException(ValidationException::class);
        $action->create([]);
    }

    public function test_create_new_user_validates_email_uniqueness(): void
    {
        $action = new CreateNewUser;
        $action->create([
            'name' => 'First User',
            'email' => 'duplicate@example.com',
            'password' => 'J0hn-Doe-Passw0rd!',
            'password_confirmation' => 'J0hn-Doe-Passw0rd!',
        ]);

        $this->expectException(\Exception::class);
        $action->create([
            'name' => 'Second User',
            'email' => 'duplicate@example.com',
            'password' => 'Sec0nd-User-Pass!',
            'password_confirmation' => 'Sec0nd-User-Pass!',
        ]);
    }

    public function test_update_user_password_updates_successfully(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);
        $this->actingAs($user);

        $action = new UpdateUserPassword;
        $action->update($user, [
            'current_password' => 'oldpassword',
            'password' => 'N3w-Password-Here!',
            'password_confirmation' => 'N3w-Password-Here!',
        ]);

        $this->assertTrue(Hash::check('N3w-Password-Here!', $user->fresh()->password));
    }

    public function test_update_user_password_validates_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('currentpassword'),
        ]);
        $this->actingAs($user);

        $action = new UpdateUserPassword;

        $this->expectException(ValidationException::class);
        $action->update($user, [
            'current_password' => 'wrongpassword',
            'password' => 'N3w-Password-Here!',
            'password_confirmation' => 'N3w-Password-Here!',
        ]);
    }

    public function test_reset_user_password_updates_password(): void
    {
        $user = User::factory()->create();
        $action = new ResetUserPassword;

        $action->reset($user, [
            'password' => 'R3set-Password-Ok!',
            'password_confirmation' => 'R3set-Password-Ok!',
        ]);

        $this->assertTrue(Hash::check('R3set-Password-Ok!', $user->fresh()->password));
    }
}
