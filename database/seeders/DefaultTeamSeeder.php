<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Branch;

class DefaultTeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $team = Team::create([
            'id' => 1,
            'name' => 'default',
            'personal_team' => false,
            'user_id' => 1,
        ]);

        Branch::create([
            'name' => 'Default Branch',
            'address' => '123 Main St, City, Country',
            'phone_number' => '+1234567890',
            'team_id' => $team->id,
        ]);
    }
}
