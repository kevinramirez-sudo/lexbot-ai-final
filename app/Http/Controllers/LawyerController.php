<?php

namespace App\Http\Controllers;

use App\Models\Abogado;
use App\Models\Caso;
use App\Models\Cita;
use App\Services\CaseWorkflowService;
use App\Services\CustomerNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class LawyerController extends Controller
{
    public function index()
    {
        $abogado = $this->abogadoAutenticado();

        if (!$abogado || $this->sinEspecialidad($abogado)) {
            return view('completarEspecialidad');
        }

        $casos = Caso::query()
            ->where('abogado', $abogado->nombre)
            ->latest('id')
            ->get();

        return view('abogadoPanel', [
            'abogado' => $abogado,
            'totalCasos' => $casos->count(),
            'casosPendientes' => $casos->filter(
                fn (Caso $caso) => !in_array(
                    app(CaseWorkflowService::class)->normalizarEstadoCaso((string) $caso->estado),
                    ['Finalizado', 'Cancelado'],
                    true
                )
            )->count(),
            'citasHoy' => Cita::query()
                ->where('abogado', $abogado->nombre)
                ->whereDate('fecha', today('America/Guayaquil'))
                ->count(),
            'clientes' => $casos->pluck('cliente')->unique()->count(),
            'casos' => $casos,
        ]);
    }

    public function verCaso(int $id)
    {
        return view('detalleCaso', [
            'caso' => $this->obtenerCasoDelAbogado($id),
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

        $caso = $this->obtenerCasoDelAbogado($id);

        try {
            $resultado = $flujo->actualizarEstado($caso, $datos['estado']);

            if ($resultado['cambio']) {
                $notificador->estadoActualizado($caso);
            }

            return redirect()
                ->route('abogado.caso.ver', $caso->id)
                ->with('success', 'Estado actualizado. El calendario, portal del cliente y Telegram fueron sincronizados.');
        } catch (Throwable $e) {
            Log::warning('No se pudo actualizar caso por abogado: '.$e->getMessage());

            return back()->with('error', 'No se pudo actualizar el estado del caso.');
        }
    }

    public function formularioCita(int $id)
    {
        return view('programarCita', [
            'caso' => $this->obtenerCasoDelAbogado($id),
        ]);
    }

    public function guardarCita(
        Request $request,
        int $id,
        CaseWorkflowService $flujo,
        CustomerNotificationService $notificador
    ) {
        $datos = $request->validate([
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
        ]);

        $caso = $this->obtenerCasoDelAbogado($id);

        try {
            $resultado = $flujo->programarCitaParaCaso(
                $caso,
                $datos['fecha'],
                $datos['hora']
            );

            if ($resultado['creada']) {
                $notificador->citaProgramada(
                    $resultado['cita'],
                    $resultado['cliente']
                );
            } elseif ($resultado['cambio']) {
                $notificador->citaReprogramada(
                    $resultado['cita'],
                    $resultado['cliente']
                );
            }

            return redirect()
                ->route('abogado.dashboard')
                ->with('success', 'Cita guardada correctamente.');
        } catch (Throwable $e) {
            Log::warning('No se pudo programar cita: '.$e->getMessage());

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function actualizarEspecialidad(Request $request)
    {
        $datos = $request->validate([
            'especialidad' => ['required', 'in:Civil,Penal,Familiar,Laboral'],
        ]);

        $abogado = $this->abogadoAutenticado();

        if (!$abogado) {
            abort(403, 'No existe un perfil de abogado vinculado a esta cuenta.');
        }

        $abogado->especialidad = $datos['especialidad'];
        $abogado->save();

        return redirect()
            ->route('abogado.dashboard')
            ->with('success', 'Especialidad guardada correctamente.');
    }

    private function abogadoAutenticado(): ?Abogado
    {
        return Abogado::query()
            ->whereRaw('LOWER(TRIM(correo)) = ?', [
                strtolower(trim((string) Auth::user()?->email)),
            ])
            ->first();
    }

    private function obtenerCasoDelAbogado(int $id): Caso
    {
        $abogado = $this->abogadoAutenticado();

        if (!$abogado) {
            abort(403, 'No se encontró el perfil del abogado.');
        }

        return Caso::query()
            ->whereKey($id)
            ->where('abogado', $abogado->nombre)
            ->firstOrFail();
    }

    private function sinEspecialidad(Abogado $abogado): bool
    {
        $especialidad = mb_strtolower(trim((string) $abogado->especialidad), 'UTF-8');

        return $especialidad === '' || $especialidad === 'sin asignar';
    }
}
