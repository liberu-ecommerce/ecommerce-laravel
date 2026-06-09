<?php

namespace Tests\Unit;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FortifyActionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeTeam(): Team
    {
        $tempUser = User::factory()->create();
        $team = new Team();
        $team->name = 'Test Team';
        $team->user_id = $tempUser->id;
        $team->personal_team = false;
        $team->save();

        // Spatie permissions need team context and the staff role
        setPermissionsTeamId($team->id);
        Role::findOrCreate('staff');

        return $team;
    }

    public function test_create_new_user_creates_user(): void
    {
        $this->makeTeam();

        $action = new CreateNewUser();
        $user = $action->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_create_new_user_hashes_password(): void
    {
        $this->makeTeam();

        $action = new CreateNewUser();
        $user = $action->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'securepassword',
            'password_confirmation' => 'securepassword',
        ]);

        $this->assertNotEquals('securepassword', $user->password);
        $this->assertTrue(Hash::check('securepassword', $user->password));
    }

    public function test_create_new_user_validates_required_fields(): void
    {
        $action = new CreateNewUser();

        $this->expectException(ValidationException::class);
        $action->create([]);
    }

    public function test_create_new_user_validates_email_uniqueness(): void
    {
        $this->makeTeam();

        $action = new CreateNewUser();
        $action->create([
            'name' => 'First User',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->expectException(\Exception::class);
        $action->create([
            'name' => 'Second User',
            'email' => 'duplicate@example.com',
            'password' => 'password456',
            'password_confirmation' => 'password456',
        ]);
    }

    public function test_update_user_password_updates_successfully(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);
        $this->actingAs($user);

        $action = new UpdateUserPassword();
        $action->update($user, [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_update_user_password_validates_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('currentpassword'),
        ]);
        $this->actingAs($user);

        $action = new UpdateUserPassword();

        $this->expectException(ValidationException::class);
        $action->update($user, [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
    }

    public function test_reset_user_password_updates_password(): void
    {
        $user = User::factory()->create();
        $action = new ResetUserPassword();

        $action->reset($user, [
            'password' => 'resetpassword123',
            'password_confirmation' => 'resetpassword123',
        ]);

        $this->assertTrue(Hash::check('resetpassword123', $user->fresh()->password));
    }
}
