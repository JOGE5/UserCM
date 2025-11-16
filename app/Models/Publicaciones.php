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

    public function categoria()
    {
        return $this->belongsTo(\App\Models\Categorias::class, 'Cod_Categoria', 'Cod_Categoria');
    }

    public function vendedor()
    {
        return $this->belongsTo(\App\Models\UsuarioCampusMarket::class, 'ID_Vendedor', 'ID_Usuario');
    }
}
