<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Mail\AccountApprovedMail;
use App\Mail\AccountRejectedMail;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return UserResource::collection(User::orderBy('id', 'desc')->paginate(15));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        if (isset($data['avatar']) && $data['avatar']) {
            $data['avatar'] = $request->file('avatar')->storeAs('...');
        }

        $user = User::create($data);

        return new UserResource($user);

    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (isset($data['avatar']) && $data['avatar']) {
            $data['avatar'] = $request->file('avatar')->storeAs('...');
        }

        $user->update($data);

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response(status: Response::HTTP_NO_CONTENT);
    }



    public function approve(Request $request, User $user): JsonResponse
    {
        if (!Auth::user()->hasRole('ADMIN')) {
            return response()->json(['message' => "Non autorisé."], 403);
        }

        if ($user->is_approved) {
            return response()->json(['message' => "Ce compte est déjà activé."], 400);
        }

        // 🔹 **Validation du compte**
        $user->update([
            'is_approved' => true,
            'activation_status' => 'approved'
        ]);

        // 🔹 **Envoyer l'email de confirmation**
        Mail::to($user->email)->send(new AccountApprovedMail($user));

        return response()->json([
            'message' => "Le compte de {$user->firstname} a été activé.",
            'status' => 200
        ]);
    }








    /**
     * ❌ Refuser un utilisateur
     */
    public function reject(Request $request, User $user): JsonResponse
    {
        // Validation des entrées
        $request->validate([
            'motif' => 'required|string|max:255'
        ], [
            'motif.required' => "Le motif de refus est obligatoire.",
            'motif.string' => "Le motif doit être une chaîne de caractères.",
            'motif.max' => "Le motif ne doit pas dépasser 255 caractères."
        ]);

        // Vérifier si le compte est déjà approuvé ou rejeté
        if ($user->activation_status === 'approved') {
            return response()->json([
                'message' => "Impossible de refuser un compte déjà approuvé.",
                'status' => 400
            ], 400);
        }

        if ($user->activation_status === 'rejected') {
            return response()->json([
                'message' => "Ce compte a déjà été refusé.",
                'status' => 400
            ], 400);
        }

        // Mise à jour du statut du compte
        $user->update([
            'activation_status' => 'rejected',
            'motif_refus' => $request->motif // Correction ici : le champ est 'motif_refus'
        ]);

        // Envoi de l'email à l'utilisateur
        Mail::to($user->email)->send(new AccountRejectedMail($user, $request->motif));

        return response()->json([
            'message' => "Le compte de l'utilisateur a été refusé avec succès.",
            'status' => 200
        ], 200);
    }





}
