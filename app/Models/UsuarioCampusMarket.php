<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Universidad;

/**
 * Class UsuarioCampusMarket
 *
 * @property int $id
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
    protected $primaryKey = 'id';
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
        'verificado',
        'experiencia',
        'nivel',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'Estado'     => 'string',
            'Genero'     => 'string',
            'Telefono'   => 'encrypted',
            'verificado' => 'boolean',
            'experiencia' => 'integer',
            'nivel' => 'integer',
        ];
    }

    /**
     * Accesor para obtener el nombre de la Insignia del usuario basado en su nivel.
     */
    public function getInsigniaAttribute()
    {
        if ($this->nivel >= 10) return 'Vendedor Élite';
        if ($this->nivel >= 5) return 'Contribuidor';
        if ($this->nivel >= 2) return 'Explorador';
        return 'Novato';
    }

    /**
     * Añade experiencia al usuario y calcula si sube de nivel.
     * Fórmula simple: cada 100 XP = 1 nivel.
     */
    public function addExperiencia(int $xp)
    {
        $this->experiencia += $xp;
        
        // Calcular nuevo nivel (ejemplo: 100 XP = Nivel 2, 200 XP = Nivel 3)
        $nuevoNivel = 1 + (int) floor($this->experiencia / 100);
        
        if ($nuevoNivel > $this->nivel) {
            $this->nivel = $nuevoNivel;
        }

        $this->save();
    }

    /**
     * Relación con el modelo User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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

    public function universidad()
    {
        return $this->belongsTo(Universidad::class, 'Cod_Universidad', 'Cod_Universidad');
    }
}
