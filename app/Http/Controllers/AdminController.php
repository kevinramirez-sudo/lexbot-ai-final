<?php

namespace App\Http\Controllers;

use App\Models\Abogado;
use App\Models\Caso;
use App\Models\Cita;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    public function index()
    {
        $casos = Caso::query()->latest('id')->limit(6)->get();

        return view('admin', [
            'totalMensajes' => Schema::hasTable('mensajes')
                ? DB::table('mensajes')->count()
                : 0,
            'totalCasos' => Caso::count(),
            'totalCitas' => Cita::count(),
            'totalClientes' => User::where('rol', 'cliente')->count(),
            'totalAbogados' => Abogado::count(),
            'casosPendientes' => Caso::query()
                ->whereNotIn('estado', ['Finalizado', 'Cancelado', 'Resuelto'])
                ->count(),
            'citasProximas' => Cita::query()
                ->whereDate('fecha', '>=', today('America/Guayaquil'))
                ->whereNotIn('estado', ['finalizada', 'cancelada'])
                ->orderBy('fecha')
                ->orderBy('hora')
                ->limit(6)
                ->get(),
            'casosRecientes' => $casos,
        ]);
    }
}
