<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Filament\Models\Contracts\HasName; // Import the HasName contract

class User extends Authenticatable implements JWTSubject, HasName // Implement HasName
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        // 'middle_name', // Eliminado
        'paternal_surname',
        'maternal_surname',
        'email',
        'phone',
        'curp',
        'password',
        'date_of_birth',
        'address',
        'photo_path',
        'email_verified_at',
        'phone_verified_at',
        'is_admin', // Añadido
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'is_admin' => 'boolean', // Añadido
        ];
    }

    public function identityDocuments()
    {
        return $this->hasMany(IdentityDocument::class);
    }

    /**
     * Get the user's full name.
     * This accessor is for general use, not directly for Filament's user name in the header.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        $fullName = trim((string) $this->first_name . ' ' . (string) $this->paternal_surname . ' ' . (string) $this->maternal_surname);
        return $fullName;
    }

    /**
     * Get the name for Filament, as required by the HasName contract.
     * This method will be used by Filament to display the user's name.
     *
     * @return string
     */
    public function getFilamentName(): string
    {
        $name = trim((string) $this->first_name . ' ' . (string) $this->paternal_surname);

        if (empty($name)) {
            $name = (string) $this->email; // Fallback to email if name parts are empty
        }

        if (empty($name)) {
            $name = 'Usuario Desconocido'; // Final fallback
        }

        Log::debug('getFilamentName() returning: ' . $name); // Keep for debugging if needed

        return $name;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
