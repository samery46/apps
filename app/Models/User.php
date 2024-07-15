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
        $allowedDomains = ['@club.co.id', '@aibm.co.id', '@gmail.com', '@ketikkan.com'];

        foreach ($allowedDomains as $domain) {
            if (str_ends_with($this->email, $domain)) {
                return true;
            }
        }

        return false;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }

    // Relasi many-to-many dengan Plant
    public function plants(): BelongsToMany
    {
        return $this->belongsToMany(Plant::class);
    }

    public function isAdmin()
    {
        // Misalnya, Anda memiliki kolom 'role' di tabel 'users'
        // dan nilai 'admin' menandakan pengguna adalah admin
        return $this->role === 'admin';
    }
}
