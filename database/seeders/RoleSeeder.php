<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Constants\PermissionsConstant;


class RoleSeeder extends Seeder
{
    /**
     * Exécuter le seeder pour créer les rôles
     */
    public function run(): void
    {
//        Role::create(['name' => 'ADMIN']);
//        echo "Role ADMIN created\n";

        Role::create(['name' => 'USER']);
        echo "Role USER created\n";


    }
}
