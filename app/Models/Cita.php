<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'citas';

    public $timestamps = false;

    protected $fillable = [
        'caso_id',
        'cliente',
        'cliente_email',
        'abogado',
        'especialidad',
        'fecha',
        'hora',
        'motivo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date:Y-m-d',
        ];
    }
}
