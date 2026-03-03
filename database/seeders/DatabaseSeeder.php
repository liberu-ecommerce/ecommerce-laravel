<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\DummyData\DummyDataSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        Artisan::call('migrate', ['--force' => true]);

        $this->call([
//            SiteSettingsSeeder::class,
            PermissionsTableSeeder::class,
            RolesSeeder::class,
            DefaultTeamSeeder::class,
            UserSeeder::class,
            MenuSeeder::class,
            SiteSettingsSeeder::class,
            DummyDataSeeder::class,
        ]);
    }
}
