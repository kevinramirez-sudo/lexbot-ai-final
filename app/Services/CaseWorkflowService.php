<?php

namespace App\Services;

use App\Models\Abogado;
use App\Models\Caso;
use App\Models\Cita;
use App\Models\Notificacion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class CaseWorkflowService
{
    public const ESTADOS_CASO = [
        'Pendiente',
        'En Proceso',
        'Finalizado',
        'Cancelado',
    ];

    public function crearCasoConCita(
        User $cliente,
        string $descripcion,
        array $analisis
    ): array {
        $abogado = $this->seleccionarAbogado(
            $analisis['especialidad']
        );

        if (!$abogado) {
            throw new RuntimeException(
                'No hay abogados disponibles para la especialidad detectada.'
            );
        }

        $horario = $this->buscarHorarioLibre(
            $abogado->nombre
        );

        if (!$horario) {
            throw new RuntimeException(
                'No hay horarios disponibles para el abogado asignado.'
            );
        }

        return DB::transaction(function () use (
            $cliente,
            $descripcion,
            $analisis,
            $abogado,
            $horario
        ) {
            $datosCaso = [
                'cliente' => $cliente->nombre,
                'abogado' => $abogado->nombre,
                'especialidad' => $analisis['especialidad'],
                'descripcion' => $descripcion,
                'prioridad' => $analisis['prioridad'],
                'estado' => 'Pendiente',
                'fecha_creacion' => now(),
            ];

            if ($this->columnaExiste('casos', 'cliente_email')) {
                $datosCaso['cliente_email'] = $cliente->email;
            }

            $caso = Caso::create($datosCaso);

            $datosCita = [
                'cliente' => $cliente->nombre,
                'abogado' => $abogado->nombre,
                'especialidad' => $analisis['especialidad'],
                'fecha' => $horario['fecha'],
                'hora' => $horario['hora'],
                'motivo' => $descripcion,
                'estado' => 'confirmada',
            ];

            if ($this->columnaExiste('citas', 'caso_id')) {
                $datosCita['caso_id'] = $caso->id;
            }

            if ($this->columnaExiste('citas', 'cliente_email')) {
                $datosCita['cliente_email'] = $cliente->email;
            }

            $cita = Cita::create($datosCita);

            $notificacion = $this->crearNotificacion(
                $cliente,
                'Nueva cita asignada',
                'Tu caso fue registrado y se asignó una cita para el '
                .$horario['fecha']
                .' a las '
                .substr($horario['hora'], 0, 5)
                .' con '
                .$abogado->nombre.'.',
                $caso->id
            );

            return compact('caso', 'cita', 'notificacion', 'abogado');
        });
    }

    public function actualizarEstado(
        Caso $caso,
        string $estado,
        ?User $cliente = null
    ): array {
        if (!in_array($estado, self::ESTADOS_CASO, true)) {
            throw new RuntimeException('El estado seleccionado no es válido.');
        }

        $estadoAnterior = $this->normalizarEstadoCaso(
            (string) $caso->estado
        );

        $cambio = $estadoAnterior !== $estado;
        $cita = null;

        DB::transaction(function () use (
            $caso,
            $estado,
            $cliente,
            $cambio,
            &$cita
        ) {
            $caso->estado = $estado;
            $caso->save();

            $cita = $this->buscarCitaDelCaso($caso);

            if ($cita) {
                $cita->estado = $this->estadoCitaPorCaso($estado);
                $cita->save();
            }

            if ($cambio) {
                $clienteReal = $cliente ?: $this->buscarClienteDelCaso($caso);

                if ($clienteReal) {
                    $this->crearNotificacion(
                        $clienteReal,
                        'Estado de caso actualizado',
                        'El estado de tu caso de '
                        .$caso->especialidad
                        .' ahora es: '
                        .$estado.'.',
                        $caso->id
                    );
                } else {
                    $this->crearNotificacionPorNombre(
                        $caso->cliente,
                        'Estado de caso actualizado',
                        'El estado de tu caso de '
                        .$caso->especialidad
                        .' ahora es: '
                        .$estado.'.',
                        $caso->id
                    );
                }
            }
        });

        return [
            'cita' => $cita,
            'cambio' => $cambio,
        ];
    }

    public function reprogramarCita(
        Cita $cita,
        string $fecha,
        string $hora
    ): bool {
        $hora = strlen($hora) === 5
            ? $hora.':00'
            : $hora;

        $ocupada = Cita::query()
            ->where('abogado', $cita->abogado)
            ->where('fecha', $fecha)
            ->where('hora', $hora)
            ->where('id', '!=', $cita->id)
            ->exists();

        if ($ocupada) {
            throw new RuntimeException(
                'Ese horario ya está ocupado para este abogado.'
            );
        }

        $cambio = $cita->fecha !== $fecha || $cita->hora !== $hora;

        if (!$cambio) {
            return false;
        }

        $cita->fecha = $fecha;
        $cita->hora = $hora;

        if (!in_array(strtolower((string) $cita->estado), ['finalizada', 'cancelada'], true)) {
            $cita->estado = 'confirmada';
        }

        $cita->save();

        $cliente = $this->buscarClienteDeLaCita($cita);

        if ($cliente) {
            $this->crearNotificacion(
                $cliente,
                'Cita reprogramada',
                'Tu cita fue reprogramada para el '
                .$fecha
                .' a las '
                .substr($hora, 0, 5)
                .' con '
                .$cita->abogado.'.',
                $this->casoIdDeCita($cita)
            );
        } else {
            $this->crearNotificacionPorNombre(
                $cita->cliente,
                'Cita reprogramada',
                'Tu cita fue reprogramada para el '
                .$fecha
                .' a las '
                .substr($hora, 0, 5)
                .' con '
                .$cita->abogado.'.',
                $this->casoIdDeCita($cita)
            );
        }

        return true;
    }

    public function programarCitaParaCaso(
        Caso $caso,
        string $fecha,
        string $hora
    ): array {
        $hora = strlen($hora) === 5 ? $hora.':00' : $hora;
        $cita = $this->buscarCitaDelCaso($caso);
        $cliente = $this->buscarClienteDelCaso($caso);
        $creada = false;

        if ($cita) {
            $cambio = $this->reprogramarCita($cita, $fecha, $hora);
        } else {
            $ocupada = Cita::query()
                ->where('abogado', $caso->abogado)
                ->where('fecha', $fecha)
                ->where('hora', $hora)
                ->exists();

            if ($ocupada) {
                throw new RuntimeException(
                    'Ese horario ya está ocupado para este abogado.'
                );
            }

            $datos = [
                'cliente' => $caso->cliente,
                'abogado' => $caso->abogado,
                'especialidad' => $caso->especialidad,
                'fecha' => $fecha,
                'hora' => $hora,
                'motivo' => $caso->descripcion,
                'estado' => 'confirmada',
            ];

            if ($this->columnaExiste('citas', 'caso_id')) {
                $datos['caso_id'] = $caso->id;
            }

            if ($this->columnaExiste('citas', 'cliente_email') && $cliente) {
                $datos['cliente_email'] = $cliente->email;
            }

            $cita = Cita::create($datos);
            $creada = true;
            $cambio = true;

            if ($cliente) {
                $this->crearNotificacion(
                    $cliente,
                    'Cita confirmada',
                    'Tu cita fue programada para el '
                    .$fecha
                    .' a las '
                    .substr($hora, 0, 5)
                    .' con el abogado '
                    .$caso->abogado.'.',
                    $caso->id
                );
            }
        }

        if ($caso->estado === 'Pendiente') {
            $caso->estado = 'En Proceso';
            $caso->save();
        }

        return [
            'cita' => $cita,
            'creada' => $creada,
            'cambio' => $cambio,
            'cliente' => $cliente,
        ];
    }

    public function buscarCitaDelCaso(Caso $caso): ?Cita
    {
        if ($this->columnaExiste('citas', 'caso_id')) {
            $cita = Cita::where('caso_id', $caso->id)
                ->latest('id')
                ->first();

            if ($cita) {
                return $cita;
            }
        }

        $base = Cita::query()
            ->whereRaw('LOWER(TRIM(cliente)) = ?', [
                mb_strtolower(trim((string) $caso->cliente), 'UTF-8'),
            ])
            ->whereRaw('LOWER(TRIM(abogado)) = ?', [
                mb_strtolower(trim((string) $caso->abogado), 'UTF-8'),
            ])
            ->whereRaw('LOWER(TRIM(especialidad)) = ?', [
                mb_strtolower(trim((string) $caso->especialidad), 'UTF-8'),
            ]);

        $cita = (clone $base)
            ->where('motivo', $caso->descripcion)
            ->latest('id')
            ->first();

        if ($cita) {
            return $cita;
        }

        return $base->latest('id')->first();
    }

    public function buscarClienteDelCaso(Caso $caso): ?User
    {
        if (
            $this->columnaExiste('casos', 'cliente_email')
            && filled($caso->cliente_email)
        ) {
            $cliente = User::whereRaw('LOWER(TRIM(email)) = ?', [
                strtolower(trim((string) $caso->cliente_email)),
            ])
                ->whereRaw('LOWER(TRIM(rol)) = ?', ['cliente'])
                ->latest('id')
                ->first();

            if ($cliente) {
                return $cliente;
            }
        }

        return User::whereRaw('LOWER(TRIM(nombre)) = ?', [
            strtolower(trim((string) $caso->cliente)),
        ])
            ->whereRaw('LOWER(TRIM(rol)) = ?', ['cliente'])
            ->latest('id')
            ->first();
    }

    public function buscarClienteDeLaCita(Cita $cita): ?User
    {
        if (
            $this->columnaExiste('citas', 'cliente_email')
            && filled($cita->cliente_email)
        ) {
            $cliente = User::whereRaw('LOWER(TRIM(email)) = ?', [
                strtolower(trim((string) $cita->cliente_email)),
            ])
                ->whereRaw('LOWER(TRIM(rol)) = ?', ['cliente'])
                ->latest('id')
                ->first();

            if ($cliente) {
                return $cliente;
            }
        }

        return User::whereRaw('LOWER(TRIM(nombre)) = ?', [
            strtolower(trim((string) $cita->cliente)),
        ])
            ->whereRaw('LOWER(TRIM(rol)) = ?', ['cliente'])
            ->latest('id')
            ->first();
    }

    public function estadoCitaPorCaso(string $estadoCaso): string
    {
        return match ($estadoCaso) {
            'Pendiente' => 'pendiente',
            'En Proceso' => 'confirmada',
            'Finalizado' => 'finalizada',
            'Cancelado' => 'cancelada',
            default => 'pendiente',
        };
    }

    public function normalizarEstadoCaso(string $estado): string
    {
        return match (mb_strtolower(trim($estado), 'UTF-8')) {
            'en proceso', 'en_proceso' => 'En Proceso',
            'finalizado', 'finalizada', 'resuelto', 'resuelta' => 'Finalizado',
            'cancelado', 'cancelada' => 'Cancelado',
            default => 'Pendiente',
        };
    }

    private function seleccionarAbogado(string $especialidad): ?Abogado
    {
        return Abogado::query()
            ->whereRaw('LOWER(TRIM(especialidad)) = ?', [
                mb_strtolower(trim($especialidad), 'UTF-8'),
            ])
            ->withCount([
                'casosAsignados as carga_actual' => function ($query) {
                    $query->whereNotIn('estado', ['Finalizado', 'Cancelado', 'Resuelto']);
                },
            ])
            ->orderBy('carga_actual')
            ->orderBy('id')
            ->first();
    }

    private function buscarHorarioLibre(string $abogado): ?array
    {
        $inicio = now('America/Guayaquil')->addDay()->startOfDay();

        for ($dia = 0; $dia < 30; $dia++) {
            $fecha = $inicio->copy()->addDays($dia);

            if ($fecha->isWeekend()) {
                continue;
            }

            foreach (range(9, 16) as $hora) {
                $horaActual = sprintf('%02d:00:00', $hora);

                $ocupada = Cita::query()
                    ->where('abogado', $abogado)
                    ->where('fecha', $fecha->format('Y-m-d'))
                    ->where('hora', $horaActual)
                    ->exists();

                if (!$ocupada) {
                    return [
                        'fecha' => $fecha->format('Y-m-d'),
                        'hora' => $horaActual,
                    ];
                }
            }
        }

        return null;
    }

    private function crearNotificacion(
        User $cliente,
        string $titulo,
        string $mensaje,
        ?int $casoId = null
    ): Notificacion {
        $datos = [
            'cliente' => $cliente->nombre,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'leida' => false,
            'fecha' => now(),
        ];

        if ($this->columnaExiste('notificaciones', 'cliente_email')) {
            $datos['cliente_email'] = $cliente->email;
        }

        if ($this->columnaExiste('notificaciones', 'caso_id')) {
            $datos['caso_id'] = $casoId;
        }

        return Notificacion::create($datos);
    }

    private function crearNotificacionPorNombre(
        string $cliente,
        string $titulo,
        string $mensaje,
        ?int $casoId = null
    ): Notificacion {
        $usuarioCliente = User::whereRaw('LOWER(TRIM(nombre)) = ?', [
            strtolower(trim($cliente)),
        ])
            ->whereRaw('LOWER(TRIM(rol)) = ?', ['cliente'])
            ->latest('id')
            ->first();

        $datos = [
            'cliente' => $usuarioCliente?->nombre ?: $cliente,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'leida' => false,
            'fecha' => now(),
        ];

        if ($this->columnaExiste('notificaciones', 'cliente_email') && $usuarioCliente) {
            $datos['cliente_email'] = $usuarioCliente->email;
        }

        if ($this->columnaExiste('notificaciones', 'caso_id')) {
            $datos['caso_id'] = $casoId;
        }

        return Notificacion::create($datos);
    }

    private function casoIdDeCita(Cita $cita): ?int
    {
        if ($this->columnaExiste('citas', 'caso_id') && $cita->caso_id) {
            return (int) $cita->caso_id;
        }

        return Caso::query()
            ->where('cliente', $cita->cliente)
            ->where('abogado', $cita->abogado)
            ->where('especialidad', $cita->especialidad)
            ->where('descripcion', $cita->motivo)
            ->latest('id')
            ->value('id');
    }

    private function columnaExiste(string $tabla, string $columna): bool
    {
        return Schema::hasTable($tabla) && Schema::hasColumn($tabla, $columna);
    }
}
