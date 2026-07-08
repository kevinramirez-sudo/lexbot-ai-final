@extends('layouts.app')

@section('content')
<div class="page-container">
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="page-kicker">Panel profesional</p><h1 class="page-title">Bienvenido, {{ $abogado->nombre }}</h1><p class="page-subtitle">Especialidad: <strong>{{ $abogado->especialidad }}</strong>. Gestiona los casos y las citas que te fueron asignados.</p></div>
        <a href="{{ route('calendario') }}" class="btn-primary">Abrir calendario</a>
    </div>

    @if(session('success'))<div class="alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert-error">{{ session('error') }}</div>@endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card"><p class="text-sm font-semibold text-slate-500">Casos asignados</p><p class="mt-2 text-4xl font-bold text-slate-900">{{ $totalCasos }}</p></div>
        <div class="stat-card"><p class="text-sm font-semibold text-slate-500">Casos activos</p><p class="mt-2 text-4xl font-bold text-slate-900">{{ $casosPendientes }}</p></div>
        <div class="stat-card"><p class="text-sm font-semibold text-slate-500">Citas de hoy</p><p class="mt-2 text-4xl font-bold text-slate-900">{{ $citasHoy }}</p></div>
        <div class="stat-card"><p class="text-sm font-semibold text-slate-500">Clientes atendidos</p><p class="mt-2 text-4xl font-bold text-slate-900">{{ $clientes }}</p></div>
    </div>

    <section class="panel mt-7 overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6"><div><h2 class="text-xl font-bold text-slate-900">Casos asignados</h2><p class="mt-1 text-sm text-slate-500">Abre un caso para actualizar su estado o programar una cita.</p></div><span class="badge border-slate-200 bg-slate-50 text-slate-700">{{ $totalCasos }} total</span></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Cliente</th><th class="px-5 py-3">Especialidad</th><th class="px-5 py-3">Prioridad</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3">Fecha</th><th class="px-5 py-3 text-right">Acción</th></tr></thead><tbody class="divide-y divide-slate-100 bg-white">
                @forelse($casos as $caso)
                    @php
                        $estado = app(App\Services\CaseWorkflowService::class)->normalizarEstadoCaso($caso->estado);
                        $estadoClass = match($estado) {'En Proceso' => 'badge-progress', 'Finalizado' => 'badge-done', 'Cancelado' => 'badge-cancelled', default => 'badge-pending'};
                        $prioridadClass = match($caso->prioridad) {'Alta' => 'badge-high', 'Baja' => 'badge-low', default => 'badge-medium'};
                    @endphp
                    <tr class="hover:bg-slate-50"><td class="px-5 py-4 font-semibold text-slate-900">{{ $caso->cliente }}</td><td class="px-5 py-4 text-slate-600">{{ $caso->especialidad }}</td><td class="px-5 py-4"><span class="badge {{ $prioridadClass }}">{{ $caso->prioridad }}</span></td><td class="px-5 py-4"><span class="badge {{ $estadoClass }}">{{ $estado }}</span></td><td class="px-5 py-4 text-slate-500">{{ optional($caso->fecha_creacion)->format('d/m/Y') ?? $caso->fecha_creacion }}</td><td class="px-5 py-4 text-right"><a href="{{ route('abogado.caso.ver', $caso->id) }}" class="text-sm font-bold text-blue-700 hover:text-blue-800">Gestionar</a></td></tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">No tienes casos asignados todavía.</td></tr>
                @endforelse
            </tbody></table>
        </div>
    </section>
</div>
@endsection
