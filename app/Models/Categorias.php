<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorias extends Model
{
    protected $table = 'categorias_articulos';
    protected $primaryKey = 'Cod_Categoria';
    
    protected $fillable = [
        'Nombre_Categoria',
        'Cod_Carrera',
    ];

    public $timestamps = true;

    /**
     * Carrera a la que pertenece la categoría (null = categoría global).
     */
    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'Cod_Carrera', 'Cod_Carrera');
    }
}
