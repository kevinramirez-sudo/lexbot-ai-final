<?php

namespace App\Services;

use App\Models\Caso;
use App\Models\Cita;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CustomerNotificationService
{
    public function casoRegistrado(User $cliente, Caso $caso, Cita $cita): void
    {
        $mensaje = "LexBot AI\n\n"
            ."Caso registrado correctamente.\n\n"
            ."Abogado asignado: ".$caso->abogado."\n"
            ."Especialidad: ".$caso->especialidad."\n"
            ."Prioridad: ".$caso->prioridad."\n\n"
            ."Cita asignada:\n"
            ."Fecha: ".$cita->fecha."\n"
            ."Hora: ".substr((string) $cita->hora, 0, 5);

        $this->enviarAlCliente($cliente, $mensaje);

        TelegramService::enviarAlAdmin(
            "LexBot AI - Nuevo caso\n\n"
            ."Cliente: ".$cliente->nombre."\n"
            ."Correo: ".$cliente->email."\n"
            ."Abogado: ".$caso->abogado."\n"
            ."Especialidad: ".$caso->especialidad."\n"
            ."Prioridad: ".$caso->prioridad
        );
    }

    public function estadoActualizado(Caso $caso, ?User $cliente = null): void
    {
        $cliente = $cliente ?: app(CaseWorkflowService::class)
            ->buscarClienteDelCaso($caso);

        if (!$cliente) {
            return;
        }

        $this->enviarAlCliente(
            $cliente,
            "LexBot AI\n\n"
            ."El estado de tu caso fue actualizado.\n\n"
            ."Especialidad: ".$caso->especialidad."\n"
            ."Abogado: ".$caso->abogado."\n"
            ."Nuevo estado: ".$caso->estado
        );
    }

    public function citaReprogramada(Cita $cita, ?User $cliente = null): void
    {
        $cliente = $cliente ?: app(CaseWorkflowService::class)
            ->buscarClienteDeLaCita($cita);

        if (!$cliente) {
            return;
        }

        $this->enviarAlCliente(
            $cliente,
            "LexBot AI\n\n"
            ."Tu cita fue reprogramada.\n\n"
            ."Abogado: ".$cita->abogado."\n"
            ."Especialidad: ".$cita->especialidad."\n"
            ."Fecha: ".$cita->fecha."\n"
            ."Hora: ".substr((string) $cita->hora, 0, 5)
        );
    }

    public function citaProgramada(Cita $cita, ?User $cliente = null): void
    {
        $cliente = $cliente ?: app(CaseWorkflowService::class)
            ->buscarClienteDeLaCita($cita);

        if (!$cliente) {
            return;
        }

        $this->enviarAlCliente(
            $cliente,
            "LexBot AI\n\n"
            ."Tu cita fue confirmada.\n\n"
            ."Abogado: ".$cita->abogado."\n"
            ."Especialidad: ".$cita->especialidad."\n"
            ."Fecha: ".$cita->fecha."\n"
            ."Hora: ".substr((string) $cita->hora, 0, 5)
        );
    }

    private function enviarAlCliente(User $cliente, string $mensaje): void
    {
        try {
            TelegramService::enviarAlCliente($cliente->email, $mensaje);
        } catch (\Throwable $e) {
            Log::warning(
                'No se pudo enviar notificación Telegram al cliente: '
                .$e->getMessage()
            );
        }
    }
}
