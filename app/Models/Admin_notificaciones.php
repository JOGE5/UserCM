<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin_notificaciones extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'admin_notificaciones';
    protected $primaryKey = 'ID_Notificacion';

    protected $fillable = [
        'tipo_envio',
        'Destinatario_Notificacion',
        'ID_Usuario',
        'Cod_Rol',
        'Titulo_Notificacion',
        'Mensaje_Notificacion',
        'imgen',
        'Estado_Notificacion',
        'Fecha_Envio',
    ];

    protected $casts = [
        'Fecha_Envio' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'ID_Usuario');
    }

    public function rol()
    {
        return $this->belongsTo(Roles::class, 'Cod_Rol', 'Cod_Rol');
    }
}
