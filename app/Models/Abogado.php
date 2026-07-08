<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Abogado extends Model
{
    protected $table = 'abogados';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'correo',
        'especialidad',
        'especialidad_id',
    ];

    public function casosAsignados(): HasMany
    {
        return $this->hasMany(Caso::class, 'abogado', 'nombre');
    }

    public function especialidadRelacion()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }
}
