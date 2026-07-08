<?php

namespace App\Http\Controllers;

use App\Models\Abogado;
use App\Models\Cita;
use App\Services\CaseWorkflowService;
use App\Services\CustomerNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CitaController extends Controller
{
    public function obtener(Request $request)
    {
        $usuario = $request->user();
        $rol = strtolower(trim((string) $usuario->rol));
        $consulta = Cita::query();

        if ($rol === 'abogado') {
            $abogado = Abogado::query()
                ->whereRaw('LOWER(TRIM(correo)) = ?', [
                    strtolower(trim((string) $usuario->email)),
                ])
                ->first();

            if (!$abogado) {
                return response()->json([]);
            }

            $consulta->whereRaw('LOWER(TRIM(abogado)) = ?', [
                strtolower(trim((string) $abogado->nombre)),
            ]);
        }

        if ($rol === 'cliente') {
            $consulta->where(function ($query) use ($usuario) {
                $query->whereRaw('LOWER(TRIM(cliente)) = ?', [
                    strtolower(trim((string) $usuario->nombre)),
                ]);

                if (Schema::hasColumn('citas', 'cliente_email')) {
                    $query->orWhereRaw('LOWER(TRIM(cliente_email)) = ?', [
                        strtolower(trim((string) $usuario->email)),
                    ]);
                }
            });
        }

        $eventos = $consulta
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get()
            ->map(fn (Cita $cita) => $this->evento($rol, $cita))
            ->filter()
            ->values();

        return response()->json($eventos);
    }

    public function actualizar(
        Request $request,
        int $id,
        CaseWorkflowService $flujo,
        CustomerNotificationService $notificador
    ) {
        $datos = $request->validate([
            'fecha' => ['required', 'date_format:Y-m-d'],
            'hora' => ['required', 'date_format:H:i'],
        ]);

        $cita = Cita::findOrFail($id);
        $this->validarPermisoEdicion($request, $cita);

        if (in_array(strtolower(trim((string) $cita->estado)), ['finalizada', 'cancelada'], true)) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No se puede reprogramar una cita finalizada o cancelada.',
            ], 422);
        }

        try {
            $cambio = $flujo->reprogramarCita(
                $cita,
                $datos['fecha'],
                $datos['hora']
            );

            if ($cambio) {
                $notificador->citaReprogramada($cita);
            }

            return response()->json([
                'success' => true,
                'fecha' => $this->fechaTexto($cita->fecha),
                'hora' => substr((string) $cita->hora, 0, 5),
                'mensaje' => $cambio ? 'Cita reprogramada correctamente.' : 'La cita ya tenía esa fecha y hora.',
            ]);
        } catch (Throwable $e) {
            Log::warning('No se pudo mover la cita: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage(),
            ], 422);
        }
    }

    private function validarPermisoEdicion(Request $request, Cita $cita): void
    {
        $usuario = $request->user();

        if (strtolower(trim((string) $usuario->rol)) !== 'abogado') {
            abort(403, 'Solo el abogado asignado puede mover esta cita.');
        }

        $abogado = Abogado::query()
            ->whereRaw('LOWER(TRIM(correo)) = ?', [
                strtolower(trim((string) $usuario->email)),
            ])
            ->first();

        if (
            !$abogado
            || strtolower(trim((string) $abogado->nombre)) !== strtolower(trim((string) $cita->abogado))
        ) {
            abort(403, 'No puedes modificar una cita de otro abogado.');
        }
    }

    private function evento(string $rol, Cita $cita): ?array
    {
        $fecha = $this->fechaTexto($cita->fecha);
        $hora = substr((string) $cita->hora, 0, 5);

        if (!$fecha || strlen($hora) !== 5) {
            return null;
        }

        return [
            'id' => $cita->id,
            'title' => match ($rol) {
                'cliente' => 'Cita con '.$cita->abogado,
                'abogado' => 'Cliente: '.$cita->cliente,
                default => $cita->cliente.' · '.$cita->abogado,
            },
            'start' => $fecha.'T'.$hora,
            'allDay' => false,
            'backgroundColor' => $this->colorEstado((string) $cita->estado),
            'borderColor' => $this->colorEstado((string) $cita->estado),
            'extendedProps' => [
                'cliente' => $cita->cliente,
                'abogado' => $cita->abogado,
                'especialidad' => $cita->especialidad,
                'fecha' => $fecha,
                'hora' => $hora,
                'motivo' => $cita->motivo,
                'estado' => $this->estadoVisual((string) $cita->estado),
            ],
        ];
    }

    private function fechaTexto($fecha): ?string
    {
        if ($fecha instanceof Carbon) {
            return $fecha->format('Y-m-d');
        }

        $fecha = trim((string) $fecha);

        if ($fecha === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $fecha, $coincidencia)) {
            return $coincidencia[0];
        }

        return null;
    }

    private function colorEstado(string $estado): string
    {
        return match (strtolower(trim($estado))) {
            'pendiente' => '#f59e0b',
            'confirmada' => '#0f766e',
            'finalizada' => '#2563eb',
            'cancelada' => '#dc2626',
            default => '#64748b',
        };
    }

    private function estadoVisual(string $estado): string
    {
        return match (strtolower(trim($estado))) {
            'confirmada' => 'Confirmada',
            'finalizada' => 'Finalizada',
            'cancelada' => 'Cancelada',
            default => 'Pendiente',
        };
    }
}
