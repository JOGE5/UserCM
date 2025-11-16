<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorias extends Model
{
    protected $table = 'categorias_articulos';
    protected $primaryKey = 'Cod_Categoria';
    
    protected $fillable = [
        'Nombre_Categoria',
    ];

    public $timestamps = true;
}
