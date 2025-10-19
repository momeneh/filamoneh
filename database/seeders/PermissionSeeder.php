<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionRole;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        // Schema::drop('centers');
        // return;

        Permission::truncate();
        PermissionRole::truncate();
        $resources = [
            'permission_role',
            'role',
            'user',
            'country',
            'province',
            'city',
            'paper',
            'person'
        ];
        $actions = [
            'view',
            'viewAny',
            'update',
            'create',
            'delete',
            'restore',
        ];
        foreach($resources as $r ){
            foreach($actions as $a){
                $permission = Permission::create(['name'=>$r.'.'.$a]);
                PermissionRole::create([
                    'role_id' => 1,
                    'permission_id' => $permission->id,
                ]);
            }
        }
       
        Schema::enableForeignKeyConstraints();
    }
}
