<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria_foros extends Model
{
    protected $table = 'categorias_foros';
    protected $primaryKey = 'Cod_Categoria';

    protected $fillable = [
        'Nombre_Categoria',
    ];

    public function foros()
    {
        return $this->hasMany(Foro::class, 'Cod_Categoria', 'Cod_Categoria');
    }
}
