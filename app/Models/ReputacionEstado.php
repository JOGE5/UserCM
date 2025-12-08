<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReputacionEstado extends Model
{
    use HasFactory;

    protected $table = 'reputacion_estado';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'estado_actual',
        'p_malo',
        'p_regular',
        'p_bueno',
        'p_excelente',
    ];

    protected $casts = [
        'p_malo' => 'decimal:6',
        'p_regular' => 'decimal:6',
        'p_bueno' => 'decimal:6',
        'p_excelente' => 'decimal:6',
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
