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
        'Foto_Universidad',
        'Descripcion_Universidad',
        'Ubicacion_Universidad',
    ];

    /**
     * Relación con el modelo Carrera.
     */
    public function carreras()
    {
        return $this->hasMany(Carrera::class, 'Cod_Universidad');
    }
}
