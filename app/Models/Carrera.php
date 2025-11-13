<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    use HasFactory;

    protected $primaryKey = 'Cod_Carrera';

    protected $fillable = [
        'Nombre_Carrera',
        'Cod_Universidad',
        'Foto_Carrera',
        'Descripcion_Carrera',
        'Duracion_Carrera',
    ];

    /**
     * Relación con el modelo Universidad.
     */
    public function universidad()
    {
        return $this->belongsTo(universidad::class, 'Cod_Universidad');
    }

    /**
     * Relación con el modelo UsuarioCampusMarket.
     */
    public function usuariosCampusMarket()
    {
        return $this->hasMany(UsuarioCampusMarket::class, 'Cod_Carrera');
    }
}
