<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'foto_base64',
        'descriptor_facial',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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
            'password' => 'hashed',
            'descriptor_facial' => 'encrypted:array',
        ];
    }

    /**
     * Avatar por defecto generado localmente (SVG data-URI), sin depender de ui-avatars.com.
     */
    protected function defaultProfilePhotoUrl(): string
    {
        $initials = collect(explode(' ', trim((string) $this->name)))
            ->filter()
            ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->take(2)
            ->implode('');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">'
            .'<rect width="100" height="100" fill="#EBF4FF"/>'
            .'<text x="50" y="52" fill="#7F9CF5" font-family="Arial, sans-serif" font-size="40" '
            .'font-weight="600" text-anchor="middle" dominant-baseline="central">'.e($initials).'</text>'
            .'</svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * Relación con el modelo UsuarioCampusMarket.
     */
    public function usuarioCampusMarket()
    {
        return $this->hasOne(UsuarioCampusMarket::class,'user_id', 'id');
    }

    /**
     * Relación con los chats del usuario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function chats()
    {
        return $this->belongsToMany(Chat::class, 'chat_users')
            ->withPivot('is_muted', 'is_hidden', 'last_read_at')
            ->withTimestamps();
    }

    /**
     * Reputaciones recibidas por este usuario
     */
    public function reputacionesRecibidas()
    {
        return $this->hasMany(ReputacionEntreUsuarios::class, 'ID_Usuario_Calificado', 'id');
    }

    /**
     * Reputaciones emitidas por este usuario
     */
    public function reputacionesEmitidas()
    {
        return $this->hasMany(ReputacionEntreUsuarios::class, 'ID_Usuario_Calificador', 'id');
    }

    /**
     * Estado de reputación del usuario
     */
    public function reputacionEstado()
    {
        return $this->hasOne(ReputacionEstado::class, 'user_id', 'id');
    }
}
