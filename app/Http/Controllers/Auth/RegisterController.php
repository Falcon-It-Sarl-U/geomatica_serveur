<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Mail\AdminApprovalNotificationMail;
use App\Mail\RegisterMail;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    //


    public function register(RegisterRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            // Vérifier si un utilisateur avec cet e-mail existe déjà
            $existingUser = User::where('email', $request->email)
                ->lockForUpdate() // Éviter les collisions concurrentielles
                ->first();

            if ($existingUser) {
                return response()->json([
                    'message' => "Un compte existe déjà avec cet e-mail.",
                    'status' => 400
                ], 400);
            }

            // Générer un code d’activation et son expiration
            $activation_code = User::generateActivationCode();
            $activation_expires_at = now()->addMinutes(30);

            // Création de l'utilisateur
            $user = User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company_name' => $request->company_name ?? null,
                'is_approved' => false,
                'email_verified_at' => null,
                'activation_status' => 'pending' // Ajout du statut
            ]);

            // Mise à jour du code d’activation
            $user->update([
                'activation_code' => $activation_code,
                'activation_code_expires_at' => $activation_expires_at
            ]);

            // Attribution du rôle USER
            $user->assignRole('USER');

            try {
                // Envoi de l'email avec le code d'activation
                Mail::to($user->email)->send(new RegisterMail($user, $activation_code));
            } catch (\Exception $e) {
                Log::error("Erreur lors de l'envoi de l'e-mail : " . $e->getMessage());
            }

            return response()->json([
                'message' => 'Un code d’activation a été envoyé à votre adresse e-mail.',
                'status' => 201
            ], 201);
        }, 3); // 3 tentatives en cas d’échec
    }




    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'activation_code' => 'required|string|size:6'
        ], [
            'email.required' => "L'email est requis.",
            'email.exists' => "Cet email n'existe pas dans notre système.",
            'activation_code.required' => "Le code d'activation est requis.",
            'activation_code.size' => "Le code d'activation doit être composé de 6 chiffres."
        ]);

        return DB::transaction(function () use ($request) {
            $user = User::where('email', $request->email)->lockForUpdate()->first();

            if (!$user) {
                return response()->json(['message' => "Utilisateur introuvable."], 404);
            }

            // 🔹 **Cas 1: L'utilisateur est déjà activé**
            if ($user->is_approved) {
                return response()->json(['message' => "Votre compte est déjà activé."], 200);
            }

            // 🔹 **Cas 2: Code expiré**
            if (!$user->activation_code || $user->activation_code_expires_at < now()) {
                $new_activation_code = User::generateActivationCode();
                $user->update([
                    'activation_code' => $new_activation_code,
                    'activation_code_expires_at' => now()->addMinutes(30)
                ]);

                Mail::to($user->email)->send(new RegisterMail($user, $new_activation_code));

                return response()->json([
                    'message' => "Votre code d'activation a expiré. Un nouveau code a été envoyé.",
                    'status' => 400
                ], 400);
            }

            // 🔹 **Cas 3: Vérification du code d'activation**
            if (!hash_equals($user->activation_code, $request->activation_code)) {
                return response()->json(['message' => "Le code d'activation est incorrect."], 400);
            }

            // 🔹 **Activation du compte en attente de validation admin**
            $user->update([
                'email_verified_at' => now(),
                'activation_code' => null,
                'activation_code_expires_at' => null,
                'activation_status' => 'pending'
            ]);

            // 🔹 **Notification à l'admin**
            $admins = User::role('ADMIN')->get();
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new AdminApprovalNotificationMail($user));
            }

            return response()->json([
                'message' => "Votre compte a été activé avec succès et est en attente de validation par l'administrateur.",
                'status' => 200
            ], 200);
        });
    }






/*
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'activation_code' => 'required|string|size:6'
        ], [
            'email.required' => "L'email est requis.",
            'email.exists' => "Cet email n'existe pas dans notre système.",
            'activation_code.required' => "Le code d'activation est requis.",
            'activation_code.size' => "Le code d'activation doit être composé de 6 chiffres."
        ]);

        // Récupérer l'utilisateur avec le bon email
        $user = User::where('email', $request->email)->first();

        // Vérifier si le code d'activation est défini et non expiré
        if (!$user->activation_code || $user->activation_code_expires_at < now()) {
            // Générer un nouveau code d'activation si l'ancien a expiré
            $new_activation_code = User::generateActivationCode();
            $user->update([
                'activation_code' => $new_activation_code,
                'activation_code_expires_at' => now()->addMinutes(30)
            ]);

            // Renvoyer le nouveau code par email
            Mail::to($user->email)->send(new RegisterMail($user, $new_activation_code));

            return response()->json([
                'message' => "Votre code d'activation a expiré. Un nouveau code a été envoyé à votre adresse e-mail.",
                'status' => 400
            ], 400);
        }

        // Comparer correctement le code d'activation
        if (!hash_equals($user->activation_code, $request->activation_code)) {
            return response()->json(['message' => "Le code d'activation est incorrect."], 400);
        }

        // Activer le compte
        $user->update([
            'is_approved' => true,
            'email_verified_at' => now(),
            'activation_code' => null,
            'activation_code_expires_at' => null
        ]);

        return response()->json([
            'message' => "Votre compte a été activé avec succès.",
            'status' => 200
        ], 200);
    }
*/

}
