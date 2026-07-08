<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use App\Models\Cita;
use App\Models\Notificacion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $cliente = $request->user();
        $nombre = strtolower(trim((string) $cliente->nombre));
        $correo = strtolower(trim((string) $cliente->email));

        $casos = Caso::query()
            ->where(function ($query) use ($nombre, $correo) {
                $query->whereRaw('LOWER(TRIM(cliente)) = ?', [$nombre]);

                if (Schema::hasColumn('casos', 'cliente_email')) {
                    $query->orWhereRaw('LOWER(TRIM(cliente_email)) = ?', [$correo]);
                }
            })
            ->latest('id')
            ->get();

        $cita = Cita::query()
            ->where(function ($query) use ($nombre, $correo) {
                $query->whereRaw('LOWER(TRIM(cliente)) = ?', [$nombre]);

                if (Schema::hasColumn('citas', 'cliente_email')) {
                    $query->orWhereRaw('LOWER(TRIM(cliente_email)) = ?', [$correo]);
                }
            })
            ->whereNotIn('estado', ['finalizada', 'cancelada'])
            ->orderBy('fecha')
            ->orderBy('hora')
            ->first();

        $notificaciones = Notificacion::query()
            ->where(function ($query) use ($nombre, $correo) {
                $query->whereRaw('LOWER(TRIM(cliente)) = ?', [$nombre]);

                if (Schema::hasColumn('notificaciones', 'cliente_email')) {
                    $query->orWhereRaw('LOWER(TRIM(cliente_email)) = ?', [$correo]);
                }
            })
            ->orderByRaw('fecha DESC NULLS LAST')
            ->latest('id')
            ->limit(8)
            ->get();

        return view('cliente', compact('casos', 'cita', 'notificaciones'));
    }
}
