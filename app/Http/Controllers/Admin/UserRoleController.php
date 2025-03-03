<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    //

        /**
     * Modifier le rôle d'un utilisateur
     */
    public function updateRole(Request $request, User $user): JsonResponse
    {
        // Vérifier que l'admin est bien authentifié
        if (!Auth::user()->hasRole('ADMIN')) {
            return response()->json([
                'message' => "Vous n'êtes pas autorisé à modifier les rôles des utilisateurs.",
                'status' => 403
            ], 403);
        }

        // Valider la requête
        $request->validate([
            'role' => 'required|string|exists:roles,name'
        ], [
            'role.required' => "Le rôle est requis.",
            'role.exists' => "Le rôle spécifié n'existe pas."
        ]);

        // Récupérer le nouveau rôle
        $newRole = Role::where('name', $request->role)->first();

        if (!$newRole) {
            return response()->json([
                'message' => "Le rôle spécifié est invalide.",
                'status' => 400
            ], 400);
        }

        // Supprimer les anciens rôles de l'utilisateur et lui attribuer le nouveau
        $user->syncRoles([$newRole->name]);

        return response()->json([
            'message' => "Le rôle de l'utilisateur a été mis à jour avec succès.",
            'user' => $user->load('roles'),
            'status' => 200
        ], 200);
    }


    /**
     * Récupérer tous les utilisateurs approuvés
     */

     public function getApprovedUsers()
     {
         $users = User::where('is_approved', true)
             ->orderBy('id', 'desc')
             ->paginate(15);

         if ($users->isEmpty()) {
             return response()->json([
                 'status' => 200,
                 'message' => 'Aucun utilisateur approuvé trouvé.',
                 'data' => []
             ]);
         }

         return response()->json([
             'status' => 200,
             'data' => UserResource::collection($users)
         ]);
     }



    /**
     * Récupérer tous les utilisateurs EN attente
     */

     public function getPendingUsers()
     {
         $users = User::where('is_approved', false)
             ->where('activation_status', 'pending')
             ->orderBy('id', 'desc')
             ->paginate(15);

         if ($users->isEmpty()) {
             return response()->json([
                 'status' => 200,
                 'message' => 'Aucun utilisateur en attente trouvé.',
                 'data' => []
             ]);
         }

         return response()->json([
             'status' => 200,
             'data' => UserResource::collection($users)
         ]);
     }


    /**
     * Récupérer tous les utilisateurs non approuvés
     */

     public function getRefusedUsers()
     {
         $users = User::where('activation_status', "rejected")
             ->orderBy('id', 'desc')
             ->paginate(15);

         if ($users->isEmpty()) {
             return response()->json([
                 'status' => 200,
                 'message' => 'Aucun utilisateur Rejeté trouvé.',
                 'data' => []
             ]);
         }

         return response()->json([
             'status' => 200,
             'data' => UserResource::collection($users)
         ]);
     }
}
