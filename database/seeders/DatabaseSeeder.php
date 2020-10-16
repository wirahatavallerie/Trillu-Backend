<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        DB::table('users')->insert(
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'john.doe',
                'password' => Hash::make('12345'),
            ]
        );

        DB::table('users')->insert([
            [
                'first_name' => 'Richard',
                'last_name' => 'Roe',
                'username' => 'richard.roe',
                'password' => Hash::make('12345'),
            ]
        ]);

        DB::table('users')->insert(
            [
                'first_name' => 'Jane',
                'last_name' => 'Poe',
                'username' => 'jane.poe',
                'password' => Hash::make('12345'),
            ]
        );
    }
}
