<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UsuarioCampusMarket
 *
 * @property int $ID_Usuario
 * @property int $user_id
 * @property string|null $Apellidos
 * @property string|null $Genero
 * @property string|null $Estado
 * @property string|null $Telefono
 * @property string|null $Foto_de_portada
 * @property string|null $Foto_de_perfil
 * @property int|null $Cod_Rol
 * @property int|null $Cod_Carrera
 * @property int|null $Cod_Universidad
 * @mixin \Eloquent
 */
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
        'Cod_Categoria_Default',
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
