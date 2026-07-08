<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caso extends Model
{
    protected $table = 'casos';

    public $timestamps = false;

    protected $fillable = [
        'cliente',
        'cliente_email',
        'abogado',
        'especialidad',
        'descripcion',
        'prioridad',
        'estado',
        'fecha_creacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_creacion' => 'datetime',
        ];
    }
}
