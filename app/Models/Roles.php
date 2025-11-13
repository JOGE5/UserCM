<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory;

    protected $primaryKey = 'Cod_Rol';

    protected $fillable = [
        'Nombre_Rol',
        'Descripcion',
        'Foto_Rol',
    ];

    /**
     * Relación con el modelo UsuarioCampusMarket.
     */
    public function usuariosCampusMarket()
    {
        return $this->hasMany(UsuarioCampusMarket::class, 'Cod_Rol');
    }
}
