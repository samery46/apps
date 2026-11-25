<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;
    use HasPanelShield;


    /**
     * Tentukan apakah user dapat mengakses panel Filament.
     *
     * @return bool
     */
    // public function canAccessPanel(): bool
    // {
    //     // Misalnya, hanya user dengan role 'admin' yang bisa mengakses panel
    //     return $this->role === 'admin';
    // }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function canAccessPanel(Panel $panel): bool
    {
        // 1. Super Admin selalu memiliki hak akses
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // 2. Daftar domain email yang diizinkan mengakses panel
        $allowedDomains = [
            '@club.co.id',
            '@aibm.co.id',
            '@gmail.com',
            '@indofood.co.id',
            '@ketikkan.com',
            '@ketik-kan.site',
            '@ketikkan.site',
            '@ymail.com',                        
            '@yahoo.co.id',            
        ];

        foreach ($allowedDomains as $domain) {
            if (str_ends_with(strtolower($this->email), strtolower($domain))) {
                return true;
            }
        }

        return false;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (! $this->avatar_url) {
            return null;
        }

        if (filter_var($this->avatar_url, FILTER_VALIDATE_URL)) {
            return $this->avatar_url;
        }

        return Storage::disk('public')->url($this->avatar_url);
    }

    // Relasi many-to-many dengan Plant
    // public function plants(): BelongsToMany
    // {
    //     return $this->belongsToMany(Plant::class);
    // }

    public function isAdmin()
    {
        // Misalnya, Anda memiliki kolom 'role' di tabel 'users'
        // dan nilai 'admin' menandakan pengguna adalah admin
        return $this->role === 'admin';
    }

    // public function hasRole(string $role): bool
    // {
    //     return $this->role === $role;
    // }


    public function plants(): BelongsToMany
    {
        return $this->belongsToMany(Plant::class, 'plant_user', 'user_id', 'plant_id');
    }

    // =========================================================================
    // RELASI UNTUK MODUL KLAIM ASET
    // =========================================================================

    public function createdClaims(): HasMany
    {
        return $this->hasMany(Claim::class, 'created_by_user_id');
    }

    public function workflowConfigs(): HasMany
    {
        return $this->hasMany(WorkflowConfig::class);
    }

    public function claimApprovals(): HasMany
    {
        return $this->hasMany(ClaimApproval::class);
    }
}
