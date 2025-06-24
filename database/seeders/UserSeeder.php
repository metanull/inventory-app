<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->isLocal()) {
            // If the application is in a local environment, run the LocalUserSeeder
            $this->call([
                LocalUserSeeder::class,
            ]);
        }
    }
}
