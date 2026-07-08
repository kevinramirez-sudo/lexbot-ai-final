@extends('layouts.app')

@section('content')
<div class="page-container">
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="page-kicker">Administración</p>
            <h1 class="page-title">Panel general</h1>
            <p class="page-subtitle">Resumen operativo en tiempo real de casos, citas, clientes y abogados.</p>
        </div>
        <a href="{{ route('calendario') }}" class="btn-primary">Abrir calendario</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="stat-card"><p class="text-sm font-semibold text-slate-500">Casos registrados</p><p class="mt-2 text-4xl font-bold text-slate-900">{{ $totalCasos }}</p><p class="mt-2 text-xs font-semibold text-amber-700">{{ $casosPendientes }} activos o pendientes</p></div>
        <div class="stat-card"><p class="text-sm font-semibold text-slate-500">Citas</p><p class="mt-2 text-4xl font-bold text-slate-900">{{ $totalCitas }}</p><p class="mt-2 text-xs text-slate-500">Agenda legal centralizada</p></div>
        <div class="stat-card"><p class="text-sm font-semibold text-slate-500">Clientes</p><p class="mt-2 text-4xl font-bold text-slate-900">{{ $totalClientes }}</p><p class="mt-2 text-xs text-slate-500">Usuarios con rol cliente</p></div>
        <div class="stat-card"><p class="text-sm font-semibold text-slate-500">Abogados</p><p class="mt-2 text-4xl font-bold text-slate-900">{{ $totalAbogados }}</p><p class="mt-2 text-xs text-slate-500">Perfiles profesionales</p></div>
        <div class="stat-card"><p class="text-sm font-semibold text-slate-500">Mensajes analizados</p><p class="mt-2 text-4xl font-bold text-slate-900">{{ $totalMensajes }}</p><p class="mt-2 text-xs text-slate-500">Historial de LexBot AI</p></div>
        <div class="rounded-2xl bg-slate-900 p-5 text-white shadow-sm"><p class="text-sm font-semibold text-slate-300">Estado del sistema</p><p class="mt-2 text-2xl font-bold">Operativo</p><p class="mt-2 text-xs text-slate-300">Portal, calendario y microservicio integrados.</p></div>
    </div>

    <div class="mt-7 grid gap-7 lg:grid-cols-2">
        <section class="panel">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div><h2 class="font-bold text-slate-900">Casos recientes</h2><p class="mt-1 text-sm text-slate-500">Últimos registros ingresados al sistema.</p></div>
                <a href="{{ route('casos-admin') }}" class="text-sm font-bold text-blue-700 hover:text-blue-800">Ver todos</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($casosRecientes as $caso)
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4"><div><p class="font-bold text-slate-900">{{ $caso->cliente }}</p><p class="mt-1 text-sm text-slate-600">{{ $caso->especialidad }} · {{ $caso->abogado }}</p></div><span class="badge {{ $caso->prioridad === 'Alta' ? 'badge-high' : ($caso->prioridad === 'Baja' ? 'badge-low' : 'badge-medium') }}">{{ $caso->prioridad }}</span></div>
                    </div>
                @empty
                    <p class="p-6 text-sm text-slate-500">No hay casos registrados.</p>
                @endforelse
            </div>
        </section>

        <section class="panel">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div><h2 class="font-bold text-slate-900">Próximas citas</h2><p class="mt-1 text-sm text-slate-500">Citas activas ordenadas por fecha.</p></div>
                <a href="{{ route('calendario') }}" class="text-sm font-bold text-blue-700 hover:text-blue-800">Calendario</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($citasProximas as $cita)
                    <div class="p-5"><p class="font-bold text-slate-900">{{ $cita->cliente }}</p><p class="mt-1 text-sm text-slate-600">{{ $cita->fecha }} · {{ substr($cita->hora, 0, 5) }} · {{ $cita->abogado }}</p><p class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-400">{{ $cita->especialidad }}</p></div>
                @empty
                    <p class="p-6 text-sm text-slate-500">No hay citas próximas.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
