<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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


}
