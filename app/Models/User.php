<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */


    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'company_name',
        'avatar',
        'email_verified_at',
        'is_approved',
        'activation_code',
        'activation_code_expires_at',
        'activation_status',
        'motif',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */

    protected $hidden = [
        'password',
        'remember_token',
        'activation_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'activation_code_expires_at' => 'datetime',
            'is_approved' => 'boolean',
            'password' => 'hashed',
        ];
    }
    public static function generateActivationCode(): string
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Vérifie si le code d'activation est valide (pas expiré).
     *
     * @return bool
     */
    public function isActivationCodeValid(): bool
    {
        return $this->activation_code && $this->activation_code_expires_at && Carbon::now()->lessThan($this->activation_code_expires_at);
    }

    /**
     * Vérifie si l'utilisateur est activé.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_approved && $this->email_verified_at !== null && $this->activation_status === 'approved';
    }
    public function regenerateActivationCode(): void
    {
        $this->update([
            'activation_code' => self::generateActivationCode(),
            'activation_code_expires_at' => now()->addMinutes(30),
        ]);
    }

    /**
     * Relation avec les sessions de l'utilisateur.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}
