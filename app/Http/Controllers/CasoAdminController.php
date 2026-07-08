<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use App\Services\CaseWorkflowService;
use App\Services\CustomerNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CasoAdminController extends Controller
{
    public function index()
    {
        $casos = Caso::query()
            ->latest('id')
            ->get();

        return view('casosAdmin', [
            'casos' => $casos,
            'totalCasos' => $casos->count(),
            'pendientes' => $casos->filter(fn (Caso $caso) => $this->estadoVisual($caso) === 'Pendiente')->count(),
            'enProceso' => $casos->filter(fn (Caso $caso) => $this->estadoVisual($caso) === 'En Proceso')->count(),
            'finalizados' => $casos->filter(fn (Caso $caso) => $this->estadoVisual($caso) === 'Finalizado')->count(),
            'cancelados' => $casos->filter(fn (Caso $caso) => $this->estadoVisual($caso) === 'Cancelado')->count(),
        ]);
    }

    public function actualizarEstado(
        Request $request,
        int $id,
        CaseWorkflowService $flujo,
        CustomerNotificationService $notificador
    ) {
        $datos = $request->validate([
            'estado' => ['required', 'in:Pendiente,En Proceso,Finalizado,Cancelado'],
        ]);

        $caso = Caso::findOrFail($id);

        try {
            $resultado = $flujo->actualizarEstado($caso, $datos['estado']);

            if ($resultado['cambio']) {
                $notificador->estadoActualizado($caso);
            }

            return back()->with('success', 'Estado del caso actualizado correctamente.');
        } catch (Throwable $e) {
            Log::warning('No se pudo actualizar estado desde administración: '.$e->getMessage());

            return back()->with('error', 'No se pudo actualizar el estado del caso.');
        }
    }

    private function estadoVisual(Caso $caso): string
    {
        return app(CaseWorkflowService::class)->normalizarEstadoCaso(
            (string) $caso->estado
        );
    }
}
