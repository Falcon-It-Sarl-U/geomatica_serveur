<?php

use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\RoleController;
use App\Http\Controllers\PermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

*/



/**
 * @OA\Info(
 *     title="Mon API Laravel",
 *     version="1.0.0",
 *     description="Documentation de l'API Laravel générée avec Swagger",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Serveur local"
 * )
 */

Route::middleware(['api.exception'])->group(function () {
    Route::prefix('v1')->name('api.')->group(function () {

        // 🔹 Authentification et Inscription
        Route::post('login', [LoginController::class, 'login'])->name('login');
        Route::post('register', [RegisterController::class, 'register'])->name('register');
        Route::post('verify', [RegisterController::class, 'verify'])->name('verify');

        Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
        Route::post('reset-password', [ResetPasswordController::class, 'reset']);



        // Routes nécessitant une authentification
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
            Route::get('/user', fn(Request $request) => $request->user());
            Route::post('/user/update-profile', [UserController::class, 'updateProfile']);
            Route::get('/user/profile', [UserController::class, 'getProfile']);

            // 🔹 Gestion des utilisateurs
            Route::apiResource('users', UserController::class);
            Route::apiResource('roles', RoleController::class);
            Route::delete('destroy_R/{role}', [RoleController::class, 'destroy_R']);
            Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');

            Route::get('/roles/{role}/permissions', [RoleController::class, 'getPermissions']);
            Route::get('/roles-with-permissions', [RoleController::class, 'getRolesWithPermissions']);

            Route::get('/auth/user', [UserController::class, 'getCurrentUser']);

            Route::put('/roles/{role}/update-permissions', [RoleController::class, 'updatePermissions'])->name('roles.updatePermissions');



            Route::get('usersRole/approved', [UserRoleController::class, 'getApprovedUsers']);
            Route::get('usersRole/pending', [UserRoleController::class, 'getPendingUsers']);
            Route::get('usersRole/rejected', [UserRoleController::class, 'getRefusedUsers']);
            Route::get('stats/users', [UserController::class, 'getUserStatistics']);
            Route::get('stats/statistics', [UserController::class, 'getUserRegistrationStats']);


            // 🔹 Activation et Rejet des utilisateurs (Accès réservé à l'ADMIN)
            Route::middleware(['role:ADMIN'])->group(function () {
                Route::post('/users/{user}/update-role', [UserRoleController::class, 'updateRole']);
            });
            // 🔹 Activation et Rejet des utilisateurs (Accès réservé à l'ADMIN)
            Route::middleware(['role:ADMIN'])->group(function () {
                Route::post('users/{user}/approve', [UserController::class, 'approve'])
                    ->name('users.approve');
                Route::post('users/{user}/reject', [UserController::class, 'reject'])
                    ->name('users.reject');
            });
        });
    });
});




Route::get('/docs', function () {
    return response()->file(storage_path('api-docs/api-docs.json'));
});
