<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ID_Foro
 * @property string $Titulo_Foro
 * @property string $Descripcion_Foro
 * @property int $ID_Creador
 * @property int $Estado_Foro
 * @property string $Imagen_Foro
 * @property int $Cod_Categoria
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Foro extends Model
{
    use HasFactory;

    protected $table = 'foros';
    protected $primaryKey = 'ID_Foro';

    protected $fillable = [
        'Titulo_Foro',
        'Descripcion_Foro',
        'ID_Creador',
        'Estado_Foro',
        'Imagen_Foro',
        'Cod_Categoria',
        'moderation_status',
        'moderation_scores',
        'moderation_checked_at',
        'moderation_reason',
    ];

    public $timestamps = true;

    public function creador()
    {
        return $this->belongsTo(User::class, 'ID_Creador');
    }

    public function categoria()
    {
        return $this->belongsTo(Categorias::class, 'Cod_Categoria');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'foro_id', 'ID_Foro');
    }
}
