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
        return $this->belongsTo(Universidad::class, 'Cod_Universidad');
    }

    /**
     * Relación con el modelo UsuarioCampusMarket.
     */
    public function usuariosCampusMarket()
    {
        return $this->hasMany(UsuarioCampusMarket::class, 'Cod_Carrera');
    }

    /**
     * Categorías de artículos asociadas a esta carrera.
     */
    public function categorias()
    {
        return $this->hasMany(Categorias::class, 'Cod_Carrera', 'Cod_Carrera');
    }
}
