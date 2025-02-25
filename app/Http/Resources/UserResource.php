<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->firstname,
            'last_name' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'company_name' => $this->company_name,
            'avatar' => $this->avatar ? url($this->avatar) : null, // Retourner l'URL de l'avatar
            'roles' => $this->roles->pluck('name'), // Retourne les rôles attribués
            'email_verified_at' => $this->email_verified_at,
            'is_approved' => $this->is_approved,
            'activation_status' => $this->activation_status,
            'motif' => $this->motif,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            // 'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            // 'last_login_at' => $this->last_login_at ? $this->last_login_at->format('Y-m-d H:i:s') : null,
            // 'ip_address' => $this->ip_address,
            // 'user_agent' => $this->user_agent,
        ];
    }
}
