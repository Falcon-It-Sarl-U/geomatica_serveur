<?php

namespace App\Console\Commands;

use App\Constants\PermissionsConstant;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $permissions = (new \ReflectionClass(PermissionsConstant::class))->getConstants();
        $permissionData = [];

        foreach ($permissions as $permission) {
            $permissionData[] = ['name' => $permission, 'guard_name' => 'web'];
        }

        Permission::upsert($permissionData, uniqueBy: ['name']);

        $adminRole = Role::firstOrCreate(
            ['name' => env('APP_ADMIN_ROLE')],
            ['description' => 'Admin Role']
        );

        $adminRole->syncPermissions(array_keys($permissions));


        $this->info('Permissions synchronized successfully!');
    }
}
