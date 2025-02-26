<?php

namespace App\Http\Controllers\Auth;

use App\Constants\PermissionsConstant;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class RoleController extends Controller
{
    public static function middleware(): array {

        return [
            new Middleware(PermissionMiddleware::using(PermissionsConstant::PERM_ROLE_VIEW), only: ['index','show']),
            new Middleware(PermissionMiddleware::using(PermissionsConstant::PERM_ROLE_UPDATE), only: ['update']),
            new Middleware(PermissionMiddleware::using(PermissionsConstant::PERM_ROLE_CREATE), only: ['store']),
            new Middleware(PermissionMiddleware::using(PermissionsConstant::PERM_ROLE_DELETE), only: ['destroy']),
        ];
    }

    public function index()
    {
        try {
            $roles = Role::all();
            return response()->json($roles, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to fetch roles'], 500);
        }
    }

    public function getPermissions(Role $role)
    {
        try {
            // Charge la relation "permissions" pour le rôle
            $role->load('permissions');
            return response()->json($role->permissions, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Impossible de récupérer les permissions pour ce rôle'], 500);
        }
    }

    public function getRolesWithPermissions()
    {
        try {
            // Charger la relation "permissions" pour tous les rôles
            $roles = Role::with('permissions')->get();
            return response()->json($roles, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Impossible de récupérer les rôles avec leurs permissions'], 500);
        }
    }



    public function store(Request $request)
    {
        $role = Role::create(['name' => $request->name]);
        return response()->json($role, 201);
    }

    // public function show(Role $role)
    // {
    //     return response()->json($role);
    // }

    public function show(Role $role)
{
    $role->load('permissions'); // Charge les permissions du rôle
    return response()->json($role);
}




    public function update(Request $request, Role $role)
    {
        $role->update(['name' => $request->name]);
        return response()->json($role);
    }
    public function destroy_R($roleName)
    {
        try {
            // Trouver le rôle par son nom
            $role = Role::where('name', $roleName)->firstOrFail();

            // Supprimer le rôle
            $role->delete();

            return response()->json(null, 204);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Impossible de supprimer ce rôle, des relations existent.'], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Rôle introuvable.'], 404);
        }
    }

    


}
