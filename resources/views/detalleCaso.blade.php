@extends('layouts.app')

@section('content')
@php
    $estado = app(App\Services\CaseWorkflowService::class)->normalizarEstadoCaso($caso->estado);
    $estadoClass = match($estado) {'En Proceso' => 'badge-progress', 'Finalizado' => 'badge-done', 'Cancelado' => 'badge-cancelled', default => 'badge-pending'};
    $prioridadClass = match($caso->prioridad) {'Alta' => 'badge-high', 'Baja' => 'badge-low', default => 'badge-medium'};
@endphp
<div class="page-container">
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="page-kicker">Panel del abogado</p><h1 class="page-title">Detalle del caso</h1><p class="page-subtitle">Gestiona el seguimiento del caso asignado sin salir de la plataforma.</p></div><a href="{{ route('abogado.dashboard') }}" class="btn-secondary">Volver al panel</a></div>
    @if(session('success'))<div class="alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert-error">{{ session('error') }}</div>@endif

    <section class="panel overflow-hidden">
        <div class="bg-gradient-to-r from-slate-950 to-blue-900 px-6 py-7 text-white"><div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-sm text-blue-200">Cliente</p><h2 class="mt-1 text-3xl font-bold">{{ $caso->cliente }}</h2></div><div class="flex flex-wrap gap-2"><span class="badge {{ $prioridadClass }}">Prioridad: {{ $caso->prioridad }}</span><span class="badge {{ $estadoClass }}">{{ $estado }}</span></div></div></div>
        <div class="panel-body">
            <div class="grid gap-4 md:grid-cols-3"><div class="rounded-xl border border-slate-200 p-4"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Especialidad</p><p class="mt-2 font-bold text-slate-900">{{ $caso->especialidad }}</p></div><div class="rounded-xl border border-slate-200 p-4"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Estado actual</p><p class="mt-2 font-bold text-slate-900">{{ $estado }}</p></div><div class="rounded-xl border border-slate-200 p-4"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Registrado</p><p class="mt-2 font-bold text-slate-900">{{ optional($caso->fecha_creacion)->format('d/m/Y H:i') ?? $caso->fecha_creacion }}</p></div></div>
            <div class="mt-7 border-t border-slate-200 pt-6"><h3 class="text-lg font-bold text-slate-900">Descripción del caso</h3><p class="mt-3 whitespace-pre-line rounded-xl bg-slate-50 p-5 leading-7 text-slate-700 ring-1 ring-slate-200">{{ $caso->descripcion }}</p></div>
            <div class="mt-7 grid gap-6 border-t border-slate-200 pt-6 lg:grid-cols-[1fr_auto]"><div><h3 class="text-lg font-bold text-slate-900">Actualizar seguimiento</h3><p class="mt-1 text-sm text-slate-600">El cambio se sincronizará con la cita, el portal y el Telegram del cliente vinculado.</p><form method="POST" action="{{ route('abogado.caso.estado', $caso->id) }}" class="mt-4 flex flex-col gap-3 sm:flex-row">@csrf<select name="estado" class="w-full rounded-xl border-slate-300 text-sm sm:w-56"><option value="Pendiente" @selected($estado === 'Pendiente')>Pendiente</option><option value="En Proceso" @selected($estado === 'En Proceso')>En Proceso</option><option value="Finalizado" @selected($estado === 'Finalizado')>Finalizado</option><option value="Cancelado" @selected($estado === 'Cancelado')>Cancelado</option></select><button type="submit" class="btn-primary">Guardar estado</button></form></div><div class="flex items-end"><a href="{{ route('abogado.cita.formulario', $caso->id) }}" class="btn-secondary">Programar o reprogramar cita</a></div></div>
        </div>
    </section>
</div>
@endsection
