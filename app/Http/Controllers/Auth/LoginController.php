<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest as AuthLoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Authentification",
 *     description="Endpoints pour l'authentification"
 * )
 */
class LoginController extends Controller
{







    /**
     * Connexion de l'utilisateur
     */

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Connexion utilisateur",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGci...")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Identifiants incorrects")
     * )
     */

    //  public function login(AuthLoginRequest $request): JsonResponse
    //  {
    //      $credentials = $request->validated();

    //      if (!Auth::attempt($credentials)) {
    //          return response()->json(['message' => 'Identifiants incorrects'], 401);
    //      }

    //      $user = Auth::user();

    //      // 🔹 **Vérification si le compte est approuvé**
    //      if (!$user->is_approved) {
    //          return response()->json([
    //              'message' => "Votre compte est en attente de validation par l'administrateur.",
    //              'status' => 403
    //          ], 403);
    //      }

    //      // 🔹 **Stocker la session**
    //     //  Session::create([
    //     //      'user_id' => $user->id,
    //     //      'ip_address' => $request->ip(),
    //     //      'user_agent' => $request->header('User-Agent'),
    //     //      'last_login_at' => now(),
    //     //  ]);

    //      // 🔹 **Générer le token API**
    //      $token = $user->createToken('AuthToken')->plainTextToken;

    //      return response()->json([
    //          'user' => new UserResource($user),
    //          'token' => $token,
    //      ]);
    //  }



    public function login(AuthLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Identifiants incorrects'], 401);
        }

        $user = Auth::user();

        // 🔹 **Vérification de l'email (Bloquant)**
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => "Veuillez vérifier votre adresse e-mail avant de vous connecter.",
                'status' => 403
            ], 403);
        }

        // 🔹 **Générer le token API**
        $token = $user->createToken('AuthToken')->plainTextToken;

        // 🔹 **Préparer la réponse**
        $response = [
            'user' => new UserResource($user),
            'token' => $token,
        ];

        // 🔹 **Ajout d'un avertissement si le compte n'est pas approuvé**
        if (!$user->is_approved) {
            $response['warning'] = "Votre compte est en attente de validation par l'administrateur.";
        }

        return response()->json($response);
    }





    /**
     * Déconnexion de l'utilisateur
     */

     public function destroy(Request $request): JsonResponse
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Utilisateur non authentifié.',
            'status' => 401
        ], 401);
    }

    // ✅ Supprime uniquement le token actuel de l'utilisateur
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'status' => 200,
        'message' => 'Déconnexion réussie'
    ], 200);
}







    // public function destroy(): JsonResponse
    // {
    //     $user = Auth::user();

    //     if ($user) {
    //         $user->tokens()->delete(); // Supprime tous les tokens de l'utilisateur
    //     }

    //     return response()->json([
    //         'status' => Response::HTTP_OK,
    //         'message' => 'Déconnexion réussie'
    //     ], Response::HTTP_OK);
    // }




}
