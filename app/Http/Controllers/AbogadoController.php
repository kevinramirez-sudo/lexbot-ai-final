<?php

namespace App\Http\Controllers;

use App\Models\Abogado;
use App\Models\Caso;

class AbogadoController extends Controller
{
    public function index()
    {
        $abogados = Abogado::query()
            ->withCount('casosAsignados')
            ->orderBy('nombre')
            ->get();

        $abogados->each(function (Abogado $abogado) {
            $especialidad = trim((string) $abogado->especialidad);
            $abogado->especialidad_visual = $especialidad === ''
                || mb_strtolower($especialidad, 'UTF-8') === 'sin asignar'
                ? 'Sin asignar'
                : mb_convert_case($especialidad, MB_CASE_TITLE, 'UTF-8');
            $abogado->casos_activos = Caso::query()
                ->where('abogado', $abogado->nombre)
                ->whereNotIn('estado', ['Finalizado', 'Cancelado', 'Resuelto'])
                ->count();
        });

        return view('abogados', [
            'abogados' => $abogados,
            'totalAbogados' => $abogados->count(),
        ]);
    }
}
