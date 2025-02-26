<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;
class PermissionController extends Controller
{
    //


    public function index(): JsonResponse
    {
        try {
            $permissions = Permission::all();

            return response()->json([
                'status' => 200,
                'message' => 'Liste des permissions récupérée avec succès.',
                'data' => $permissions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la récupération des permissions.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
