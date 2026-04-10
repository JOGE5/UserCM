<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Universidad extends Model
{
    use HasFactory;

    protected $table = 'universidades';

    protected $primaryKey = 'Cod_Universidad';

    protected $fillable = [
        'Nombre_Universidad',
        'Correo_Universidad',
        'Telefono_Universidad',
        'Direccion_Universidad',
        'Universisdad_foto_de_portada',
        'Universisdad_foto_de_perfil',
        'Sitio_Web',
        'Descripcion',
        'Hora_apertura',
        'Hora_cierre',
    ];

    /**
     * Relación con el modelo Carrera.
     */
    public function carreras()
    {
        return $this->hasMany(Carrera::class, 'Cod_Universidad');
    }
}
