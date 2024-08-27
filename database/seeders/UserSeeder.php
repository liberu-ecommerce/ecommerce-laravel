<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $adminUser->assignRole('admin');

        $staffUser = User::create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $staffUser->assignRole('staff');

        // Create teams for admin and staff users
       $this->createTeamForUser($adminUser);
        $this->createTeamForUser($staffUser);

        // Create additional users with teams
   //     User::factory(8)->create()->each(function ($user) {
   //         $this->createTeamForUser($user);
//        });
    }

    private function createTeamForUser($user)
    {
        $team = Team::first();
        $team->users()->attach($user);

        $user->current_team_id = 1;
        $user->save();
    }
}
