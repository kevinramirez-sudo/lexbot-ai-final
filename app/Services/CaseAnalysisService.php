<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class CaseAnalysisService
{
    public function analizar(string $descripcion): array
    {
        $baseUrl = config(
            'services.lexbot.analisis_url',
            'http://127.0.0.1:8003'
        );

        $respuesta = Http::acceptJson()
            ->timeout(10)
            ->post(
                rtrim((string) $baseUrl, '/').'/analizar-caso',
                [
                    'descripcion' => $descripcion,
                ]
            );

        if (!$respuesta->successful()) {
            throw new RuntimeException(
                'El servicio de análisis respondió con código '
                .$respuesta->status().'.'
            );
        }

        $datos = $respuesta->json();

        $especialidad = trim((string) ($datos['especialidad'] ?? ''));
        $prioridad = trim((string) ($datos['prioridad'] ?? ''));

        if ($especialidad === '' || $prioridad === '') {
            throw new RuntimeException(
                'El servicio de análisis devolvió una respuesta incompleta.'
            );
        }

        return [
            'especialidad' => $this->normalizarEspecialidad($especialidad),
            'prioridad' => $this->normalizarPrioridad($prioridad),
            'motivo' => trim((string) ($datos['motivo'] ?? 'Análisis preliminar completado.')),
            'palabras_detectadas' => is_array($datos['palabras_detectadas'] ?? null)
                ? $datos['palabras_detectadas']
                : [],
        ];
    }

    private function normalizarEspecialidad(string $especialidad): string
    {
        $valor = mb_strtolower($especialidad, 'UTF-8');

        return match ($valor) {
            'penal' => 'Penal',
            'familiar' => 'Familiar',
            'laboral' => 'Laboral',
            'civil' => 'Civil',
            default => mb_convert_case($especialidad, MB_CASE_TITLE, 'UTF-8'),
        };
    }

    private function normalizarPrioridad(string $prioridad): string
    {
        return match (mb_strtolower($prioridad, 'UTF-8')) {
            'alta' => 'Alta',
            'baja' => 'Baja',
            default => 'Media',
        };
    }
}
