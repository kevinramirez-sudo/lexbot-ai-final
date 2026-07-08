@extends('layouts.app')

@section('content')
<div class="page-container">
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="page-kicker">Portal del cliente</p>
            <h1 class="page-title">Hola, {{ auth()->user()->nombre }}</h1>
            <p class="page-subtitle">Consulta el estado de tus casos, tus citas y recibe orientación inicial con LexBot AI.</p>
        </div>
        <a href="{{ route('calendario') }}" class="btn-secondary">Ver mi calendario</a>
    </div>

    @if($cita)
        <section class="mb-7 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-800 to-blue-600 text-white shadow-lg">
            <div class="flex flex-col gap-5 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-200">Próxima cita</p>
                    <h2 class="mt-2 text-2xl font-bold">{{ $cita->especialidad }} · {{ $cita->abogado }}</h2>
                    <p class="mt-2 text-sm text-blue-100">{{ $cita->fecha }} a las {{ substr($cita->hora, 0, 5) }}</p>
                </div>
                <span class="rounded-xl bg-white/15 px-4 py-3 text-sm font-semibold ring-1 ring-white/20">{{ ucfirst($cita->estado) }}</span>
            </div>
        </section>
    @else
        <section class="mb-7 rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-slate-600">
            Todavía no tienes una cita próxima. Registra un caso con LexBot AI para iniciar el proceso.
        </section>
    @endif

    <div class="grid gap-7 lg:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)]">
        <section class="panel overflow-hidden">
            <div class="border-b border-slate-200 bg-slate-900 px-5 py-5 text-white sm:px-6">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-lg font-bold">L</span>
                    <div>
                        <h2 class="text-xl font-bold">LexBot AI</h2>
                        <p class="mt-1 text-sm text-slate-300">Describe tu situación. Primero recibirás un análisis y tú decidirás si deseas registrar el caso.</p>
                    </div>
                </div>
            </div>

            <div class="p-5 sm:p-6">
                <div id="chat" class="mb-4 h-72 space-y-4 overflow-y-auto rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                    <div class="flex">
                        <div class="max-w-[85%] rounded-2xl rounded-tl-sm bg-white p-3 text-sm leading-6 text-slate-700 shadow-sm ring-1 ring-slate-200">
                            Hola, soy <strong>LexBot AI</strong>. Cuéntame tu problema jurídico y analizaré la especialidad y prioridad antes de registrar el caso.
                        </div>
                    </div>
                </div>

                <label for="mensaje" class="mb-2 block text-sm font-bold text-slate-800">Describe tu caso</label>
                <textarea id="mensaje" rows="5" class="block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600" placeholder="Ejemplo: Me despidieron de mi trabajo y no me quieren pagar mi sueldo."></textarea>

                <div class="mt-4 flex flex-wrap gap-3">
                    <button id="btnAnalizar" type="button" class="btn-primary">Analizar caso</button>
                    <button id="btnRegistrar" type="button" class="hidden rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Registrar este caso</button>
                </div>
                <p id="estadoChat" class="mt-3 hidden text-sm font-medium text-slate-500"></p>
            </div>
        </section>

        <aside class="space-y-7">
            <section class="panel">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="font-bold text-slate-900">Notificaciones</h2>
                </div>
                <div class="max-h-80 divide-y divide-slate-100 overflow-y-auto">
                    @forelse($notificaciones as $notificacion)
                        <div class="p-5">
                            <p class="font-semibold text-slate-900">{{ $notificacion->titulo }}</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $notificacion->mensaje }}</p>
                            <p class="mt-2 text-xs font-medium text-slate-400">{{ optional($notificacion->fecha)->format('d/m/Y H:i') ?? $notificacion->fecha }}</p>
                        </div>
                    @empty
                        <p class="p-5 text-sm text-slate-500">No tienes notificaciones por ahora.</p>
                    @endforelse
                </div>
            </section>

            <section class="panel">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="font-bold text-slate-900">Canales de atención</h2>
                </div>
                <div class="space-y-3 p-5 text-sm text-slate-600">
                    <p><strong class="text-slate-800">Portal web:</strong> registra y consulta tus casos.</p>
                    <p><strong class="text-slate-800">Telegram:</strong> usa el mismo correo con el que creaste tu cuenta para recibir avisos.</p>
                </div>
            </section>
        </aside>
    </div>

    <section class="panel mt-7 overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Mis casos</h2>
                <p class="mt-1 text-sm text-slate-500">Seguimiento de los casos registrados en tu cuenta.</p>
            </div>
            <span class="badge border-slate-200 bg-slate-50 text-slate-700">{{ $casos->count() }} casos</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Especialidad</th>
                        <th class="px-5 py-3">Abogado</th>
                        <th class="px-5 py-3">Prioridad</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3">Registro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($casos as $caso)
                        @php
                            $estado = app(App\Services\CaseWorkflowService::class)->normalizarEstadoCaso($caso->estado);
                            $estadoClass = match($estado) {
                                'En Proceso' => 'badge-progress',
                                'Finalizado' => 'badge-done',
                                'Cancelado' => 'badge-cancelled',
                                default => 'badge-pending',
                            };
                            $prioridadClass = match($caso->prioridad) {
                                'Alta' => 'badge-high',
                                'Baja' => 'badge-low',
                                default => 'badge-medium',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 font-semibold text-slate-900">{{ $caso->especialidad }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $caso->abogado }}</td>
                            <td class="px-5 py-4"><span class="badge {{ $prioridadClass }}">{{ $caso->prioridad }}</span></td>
                            <td class="px-5 py-4"><span class="badge {{ $estadoClass }}">{{ $estado }}</span></td>
                            <td class="px-5 py-4 text-slate-500">{{ optional($caso->fecha_creacion)->format('d/m/Y') ?? $caso->fecha_creacion }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">Aún no tienes casos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const chat = document.getElementById('chat');
    const input = document.getElementById('mensaje');
    const analizar = document.getElementById('btnAnalizar');
    const registrar = document.getElementById('btnRegistrar');
    const estado = document.getElementById('estadoChat');
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let pendiente = null;

    const escapar = (texto) => {
        const div = document.createElement('div');
        div.textContent = texto ?? '';
        return div.innerHTML;
    };

    const agregar = (texto, tipo = 'bot') => {
        const fila = document.createElement('div');
        fila.className = tipo === 'usuario' ? 'flex justify-end' : 'flex';
        const burbuja = document.createElement('div');
        burbuja.className = tipo === 'usuario'
            ? 'max-w-[85%] rounded-2xl rounded-tr-sm bg-blue-700 p-3 text-sm leading-6 text-white shadow-sm'
            : 'max-w-[85%] rounded-2xl rounded-tl-sm bg-white p-3 text-sm leading-6 text-slate-700 shadow-sm ring-1 ring-slate-200';
        burbuja.innerHTML = texto;
        fila.appendChild(burbuja);
        chat.appendChild(fila);
        chat.scrollTop = chat.scrollHeight;
        return fila;
    };

    const solicitar = async (url, body) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify(body),
        });
        const data = await response.json().catch(() => ({ success: false, mensaje: 'El servidor devolvió una respuesta inválida.' }));
        if (!response.ok || !data.success) throw new Error(data.mensaje || 'No se pudo procesar la solicitud.');
        return data;
    };

    analizar.addEventListener('click', async () => {
        const mensaje = input.value.trim();
        if (mensaje.length < 10) {
            agregar('Describe el caso con un poco más de detalle para poder analizarlo.');
            return;
        }
        agregar(escapar(mensaje), 'usuario');
        input.value = '';
        analizar.disabled = true;
        registrar.classList.add('hidden');
        estado.classList.remove('hidden');
        estado.textContent = 'Analizando caso...';
        const cargando = agregar('LexBot AI está analizando la especialidad y prioridad...');

        try {
            const data = await solicitar('{{ route('casos.analizar') }}', { mensaje });
            cargando.remove();
            pendiente = { mensaje, ...data };
            agregar(
                '<strong>Análisis preliminar listo.</strong><br><br>' +
                'Especialidad detectada: <strong>' + escapar(data.especialidad) + '</strong><br>' +
                'Prioridad: <strong>' + escapar(data.prioridad) + '</strong><br><br>' +
                escapar(data.motivo) + '<br><br>' +
                'Si deseas continuar, presiona <strong>Registrar este caso</strong>.'
            );
            registrar.classList.remove('hidden');
        } catch (error) {
            cargando.remove();
            agregar('<strong>No se pudo analizar el caso.</strong><br>' + escapar(error.message));
        } finally {
            analizar.disabled = false;
            estado.classList.add('hidden');
        }
    });

    registrar.addEventListener('click', async () => {
        if (!pendiente) return;
        registrar.disabled = true;
        registrar.textContent = 'Registrando...';
        try {
            const data = await solicitar('{{ route('casos.guardar') }}', { mensaje: pendiente.mensaje });
            agregar(
                '<strong>Tu caso fue registrado correctamente.</strong><br><br>' +
                'Abogado asignado: <strong>' + escapar(data.abogado) + '</strong><br>' +
                'Especialidad: <strong>' + escapar(data.especialidad) + '</strong><br>' +
                'Prioridad: <strong>' + escapar(data.prioridad) + '</strong><br>' +
                'Cita: <strong>' + escapar(data.fecha) + ' · ' + escapar(data.hora) + '</strong>'
            );
            pendiente = null;
            registrar.classList.add('hidden');
            setTimeout(() => window.location.reload(), 1300);
        } catch (error) {
            agregar('<strong>No se pudo registrar el caso.</strong><br>' + escapar(error.message));
        } finally {
            registrar.disabled = false;
            registrar.textContent = 'Registrar este caso';
        }
    });
});
</script>
@endsection
