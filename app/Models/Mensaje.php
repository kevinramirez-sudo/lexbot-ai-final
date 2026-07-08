<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    protected $table = 'mensajes';

    public $timestamps = false;

    protected $fillable = [
        'nombre_cliente',
        'correo_cliente',
        'canal',
        'mensaje',
        'categoria',
        'prioridad',
        'respuesta_ia',
        'resumen',
        'abogado_asignado',
        'created_at',
    ];
}
