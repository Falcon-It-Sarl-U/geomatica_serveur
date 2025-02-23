<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Exécuter le seeder pour créer un utilisateur admin
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            [
                'email' => env('APP_USER_EMAIL', 'admin@example.com'),
            ],
            [
                'firstname' => 'Admin',
                'lastname' => 'User',
                'company_name' => 'AdminCorp',
                'avatar' => null, // Peut être modifié avec une URL
                // 'disable_at' => null, // Pas de désactivation par défaut
                'email_verified_at' => now(),
                'is_approved' => true, // Admin approuvé directement
                'password' => Hash::make(env('APP_USER_PASSWORD', 'password123')),
                'remember_token' => Str::random(10),
            ]
        );

        // Récupération du rôle ADMIN
        $adminRole = Role::where('name', 'ADMIN')->first();

        if (!$adminRole) {
            $this->command->error("Le rôle ADMIN n'existe pas. Exécutez d'abord `php artisan db:seed --class=RoleSeeder`.");
            exit;
        }

        // Assigner le rôle ADMIN à l'utilisateur
        $user->syncRoles($adminRole);
        echo "Admin user created and assigned ADMIN role\n";
    }
}
