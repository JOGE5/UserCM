<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioCampusMarket extends Model
{
    use HasFactory;

    protected $table = 'usuarios_campus_markets';
    protected $primaryKey = 'ID_Usuario';
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'Apellidos',
        'Genero',
        'Estado',
        'Telefono',
        'Foto_de_portada',
        'Foto_de_perfil',
        'Cod_Rol',
        'Cod_Carrera',
        'Cod_Universidad',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'Estado' => 'string',
            'Genero' => 'string',
        ];
    }

    /**
     * Relación con el modelo User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el modelo Rol (asumiendo que existe).
     */
    public function rol()
    {
        return $this->belongsTo(Roles::class, 'Cod_Rol');
    }

    /**
     * Relación con el modelo Carrera (asumiendo que existe).
     */
    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'Cod_Carrera');
    }
}
