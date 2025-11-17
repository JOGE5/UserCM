<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $publicacion_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Favorito extends Model
{
    protected $table = 'favoritos';

    protected $fillable = [
        'user_id',
        'publicacion_id',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function publicacion(): BelongsTo
    {
        return $this->belongsTo(Publicaciones::class, 'publicacion_id', 'id');
    }
}
