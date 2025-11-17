<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Publicaciones
 *
 * @property int $id
 * @property int $ID_Publicacion
 * @property string $Titulo_Publicacion
 * @property string $Descripcion_Publicacion
 * @property bool|null $Estado_Publicacion
 * @property float|null $Precio_Publicacion
 * @property string|null $Imagen_Publicacion
 * @property int|null $Cod_Categoria
 * @property int|null $ID_Vendedor
 * @property string $estado (activa, borrador, eliminada)
 * @mixin \Eloquent
 */
class Publicaciones extends Model
{
    use SoftDeletes;
    /**
     * Cast attributes to native types for better static analysis and correctness.
     *
     * @var array
     */
    protected $casts = [
        'Estado_Publicacion' => 'boolean',
        'Precio_Publicacion' => 'float',
    ];
    protected $fillable = [
        'Titulo_Publicacion',
        'Descripcion_Publicacion',
        'Estado_Publicacion',
        'Precio_Publicacion',
        'Imagen_Publicacion',
        'Cod_Categoria',
        'ID_Vendedor',
        'estado',
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
