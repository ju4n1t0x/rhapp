<?php

namespace Database\Seeders;

use Database\Seeders\CitiesTableSeeder;
use Database\Seeders\ProvincesTableSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{


    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        /*
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        */
        DB::table('users')->delete();

        $this->call([
            ProvincesTableSeeder::class,
            CitiesTableSeeder::class,
        ]);

        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@rhapp.com',
            'password' => Hash::make('Ch4mpa1803*'),
        ]);
    }
}
