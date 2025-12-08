<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReputacionEntreUsuarios extends Model
{
    use HasFactory;

    protected $table = 'reputacion_entre_usuarios';
    protected $primaryKey = 'ID_Reputacion';
    public $timestamps = true;
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'ID_Usuario_Calificador',
        'ID_Usuario_Calificado',
        'Puntuacion',
        'Comentario',
    ];

    protected $casts = [
        'Puntuacion' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el usuario que califica
     */
    public function usuarioCalificador()
    {
        return $this->belongsTo(User::class, 'ID_Usuario_Calificador', 'id');
    }

    /**
     * Relación con el usuario que es calificado
     */
    public function usuarioCalificado()
    {
        return $this->belongsTo(User::class, 'ID_Usuario_Calificado', 'id');
    }
}
