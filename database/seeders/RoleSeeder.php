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
        $adminRole = Role::firstOrCreate(['name' => 'ADMIN']);
        echo "Role ADMIN created\n";



        $userRole = Role::firstOrCreate(['name' => 'USER']);
        echo "Role USER created\n";


        $permissions = [
            PermissionsConstant::PERM_USERS_VIEW,
            PermissionsConstant::PERM_USERS_CREATE,
            PermissionsConstant::PERM_USERS_UPDATE,
            PermissionsConstant::PERM_USERS_DELETE,
            PermissionsConstant::PERM_USERS_VALIDATE,
        ];

        // Attribution des permissions aux rôles
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }


         // L'ADMIN a toutes les permissions
         $adminRole->syncPermissions($permissions);
         $userRole->syncPermissions([PermissionsConstant::PERM_USERS_VIEW, PermissionsConstant::PERM_USERS_VALIDATE]);

         echo "Permissions assigned to roles.\n";


    }
}
