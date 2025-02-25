<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
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

    public function show(Role $role)
    {
        return response()->json($role);
    }

    public function update(Request $request, Role $role)
    {
        $role->update(['name' => $request->name]);
        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(null, 204);
    }
}
