<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use App\Models\Cita;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = User::query()
            ->where('rol', 'cliente')
            ->orderBy('nombre')
            ->get();

        $clientes->each(function (User $cliente) {
            $cliente->total_casos = Caso::query()
                ->where(function ($query) use ($cliente) {
                    $query->where('cliente', $cliente->nombre);

                    if (Schema::hasColumn('casos', 'cliente_email')) {
                        $query->orWhereRaw('LOWER(TRIM(cliente_email)) = ?', [
                            strtolower(trim($cliente->email)),
                        ]);
                    }
                })
                ->count();

            $cliente->total_citas = Cita::query()
                ->where(function ($query) use ($cliente) {
                    $query->where('cliente', $cliente->nombre);

                    if (Schema::hasColumn('citas', 'cliente_email')) {
                        $query->orWhereRaw('LOWER(TRIM(cliente_email)) = ?', [
                            strtolower(trim($cliente->email)),
                        ]);
                    }
                })
                ->count();
        });

        return view('clientes', [
            'clientes' => $clientes,
            'totalClientes' => $clientes->count(),
        ]);
    }
}
