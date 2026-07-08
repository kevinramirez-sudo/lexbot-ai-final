<?php

namespace App\Http\Controllers;

use App\Mail\CitaAsignadaMail;
use App\Models\Mensaje;
use App\Services\CaseAnalysisService;
use App\Services\CaseWorkflowService;
use App\Services\CustomerNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CasoController extends Controller
{
    public function analizar(Request $request, CaseAnalysisService $analizador)
    {
        $datos = $request->validate([
            'mensaje' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        try {
            $analisis = $analizador->analizar($datos['mensaje']);

            return response()->json([
                'success' => true,
                ...$analisis,
            ]);
        } catch (Throwable $e) {
            Log::warning('No se pudo analizar el caso: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'El servicio de análisis jurídico no está disponible. Verifica que FastAPI esté ejecutándose en el puerto 8003.',
            ], 503);
        }
    }

    public function guardar(
        Request $request,
        CaseAnalysisService $analizador,
        CaseWorkflowService $flujo,
        CustomerNotificationService $notificador
    ) {
        $datos = $request->validate([
            'mensaje' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $cliente = $request->user();

        if ($cliente->rol !== 'cliente') {
            abort(403, 'Solo los clientes pueden registrar casos.');
        }

        try {
            $analisis = $analizador->analizar($datos['mensaje']);
            $resultado = $flujo->crearCasoConCita(
                $cliente,
                $datos['mensaje'],
                $analisis
            );
        } catch (Throwable $e) {
            Log::warning('Error al registrar caso: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage(),
            ], 422);
        }

        $this->guardarMensajeAnalizado(
            $cliente->nombre,
            $cliente->email,
            $datos['mensaje'],
            $analisis,
            $resultado['abogado']->nombre
        );

        $notificador->casoRegistrado(
            $cliente,
            $resultado['caso'],
            $resultado['cita']
        );

        try {
            Mail::to($cliente->email)->send(
                new CitaAsignadaMail(
                    $cliente->nombre,
                    $resultado['caso']->abogado,
                    $resultado['caso']->especialidad,
                    $resultado['cita']->fecha,
                    $resultado['cita']->hora
                )
            );
        } catch (Throwable $e) {
            Log::info('Correo no enviado: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'caso_id' => $resultado['caso']->id,
            'abogado' => $resultado['caso']->abogado,
            'especialidad' => $resultado['caso']->especialidad,
            'prioridad' => $resultado['caso']->prioridad,
            'fecha' => $resultado['cita']->fecha,
            'hora' => substr((string) $resultado['cita']->hora, 0, 5),
        ]);
    }

    private function guardarMensajeAnalizado(
        string $nombre,
        string $correo,
        string $mensaje,
        array $analisis,
        string $abogado
    ): void {
        if (!Schema::hasTable('mensajes')) {
            return;
        }

        try {
            Mensaje::create([
                'nombre_cliente' => $nombre,
                'correo_cliente' => $correo,
                'canal' => 'web',
                'mensaje' => $mensaje,
                'categoria' => $analisis['especialidad'],
                'prioridad' => $analisis['prioridad'],
                'respuesta_ia' => $analisis['motivo'],
                'resumen' => 'Caso registrado desde el portal LexBot AI.',
                'abogado_asignado' => $abogado,
            ]);
        } catch (Throwable $e) {
            Log::info('No se pudo guardar el historial de análisis: '.$e->getMessage());
        }
    }
}
