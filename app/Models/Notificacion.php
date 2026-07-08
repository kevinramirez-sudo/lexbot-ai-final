<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    public $timestamps = false;

    protected $fillable = [
        'caso_id',
        'cliente',
        'cliente_email',
        'titulo',
        'mensaje',
        'leida',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'leida' => 'boolean',
            'fecha' => 'datetime',
        ];
    }
}
