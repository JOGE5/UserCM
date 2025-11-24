<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $foro_id
 * @property int $user_id
 * @property string $texto
 * @property \Illuminate\Support\Carbon $created_at
 */
class Comentario extends Model
{
    use HasFactory;

    protected $table = 'comentarios';

    protected $fillable = ['foro_id', 'user_id', 'texto'];

    public function foro(): BelongsTo
    {
        return $this->belongsTo(foros::class, 'foro_id', 'ID_Foro');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
