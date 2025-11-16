<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publicaciones extends Model
{
    protected $fillable = [
        'Titulo_Publicacion',
        'Descripcion_Publicacion',
        'Estado_Publicacion',
        'Precio_Publicacion',
        'Imagen_Publicacion',
        'Cod_Categoria',
        'ID_Vendedor',
    ];
}
