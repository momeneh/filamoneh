<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RoleUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'id'=>1,
            'title' => 'admin',
        ]);
        Role::create([
            'id'=>2,
            'title' => 'admin2',
        ]);
        RoleUser::create([
            'role_id' => 1,
            'user_id' => 1,
        ]);
        RoleUser::create([
            'role_id' => 2,
            'user_id' => 2,
        ]);
    }
}
