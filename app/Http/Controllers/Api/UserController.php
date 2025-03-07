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
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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


    public function getCurrentUser(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => "Utilisateur non trouvé."
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => "Informations de l'utilisateur récupérées avec succès.",
            'data' => [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'phone' => $user->phone,
                'company_name' => $user->company_name,
                'is_approved' => $user->is_approved,
                'activation_status' => $user->activation_status, // Ajout du statut
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ], 200);
    }

    public function getUserStatistics(): JsonResponse
    {
        $totalUsers = User::count();
        $approvedUsers = User::where('is_approved', true)->count();
        $pendingUsers = User::where('activation_status', 'pending')->count();
        $rejectedUsers = User::where('activation_status', 'rejected')->count();

        return response()->json([
            'status' => 200,
            'message' => 'Statistiques des utilisateurs récupérées avec succès.',
            'data' => [
                'total_users' => $totalUsers,
                'approved_users' => $approvedUsers,
                'pending_users' => $pendingUsers,
                'rejected_users' => $rejectedUsers
            ]
        ]);
    }
    public function getUserRegistrationStats()
    {
        $monthlyRegistrations = User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // 📌 Formatage pour que chaque mois ait une valeur (même 0 si pas d'inscription)
        $formattedRegistrations = array_fill(1, 12, 0);
        foreach ($monthlyRegistrations as $month => $count) {
            $formattedRegistrations[$month - 1] = $count; // Index basé sur 0 pour Angular
        }

        return response()->json([
            'status' => 200,
            'message' => 'Inscriptions mensuelles récupérées avec succès',
            'data' => [
                'monthly_registrations' => array_values($formattedRegistrations)
            ]
        ]);
    }




    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Validation stricte des données
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|regex:/^\+?[0-9]{7,15}$/|max:15|unique:users,phone,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048' // Max 2MB
        ]);

        // Mise à jour des champs autorisés
        $user->firstname = $request->input('firstname');
        $user->lastname = $request->input('lastname');
        $user->company_name = $request->input('company_name');
        $user->phone = $request->input('phone');

        // Gestion de l'avatar (Upload sécurisé)
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');

            // Supprimer l'ancien avatar si existant
            if ($user->avatar) {
                Storage::delete('public/avatars/' . $user->avatar);
            }

            // Générer un nom de fichier unique
            $avatarName = time() . '_' . $avatar->getClientOriginalName();
            $avatar->storeAs('public/avatars', $avatarName);

            $user->avatar = $avatarName;
        }

        $user->save();

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user' => [
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'company_name' => $user->company_name,
                'phone' => $user->phone,
                'avatar' => $user->avatar ? asset('storage/avatars/' . $user->avatar) : null
            ]
        ]);
    }

    public function getProfile()
    {
        $user = Auth::user();

        return response()->json([
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'company_name' => $user->company_name,
            'phone' => $user->phone,
            'avatar' => $user->avatar ? asset('storage/avatars/' . $user->avatar) : null
        ]);
    }


}
