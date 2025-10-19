<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\Subject;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => fake()->name(),
            'email' => 'admin@filamoneh.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password')
        ]);
        User::factory(10)->create([
            'password' => Hash::make('password'),
        ]);
        Person::factory(10)->create();

        Subject::create(['title'=>'هوش مصنوعی']);
        Subject::create(['title'=>'نانو']);
        $this->call([
            RoleSeeder::class,
			PermissionSeeder::class,
            CountrySeeder::class
        ]);
       
    }
}
