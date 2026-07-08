@extends('layouts.app')

@section('content')
<div class="page-container">
    <div class="mb-7"><p class="page-kicker">Administración</p><h1 class="page-title">Gestión de casos</h1><p class="page-subtitle">Actualiza los estados desde una única lista. Los cambios se reflejan en la cita, calendario y notificaciones.</p></div>
    @if(session('success'))<div class="alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert-error">{{ session('error') }}</div>@endif
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5"><div class="stat-card"><p class="text-sm text-slate-500">Total</p><p class="mt-2 text-3xl font-bold">{{ $totalCasos }}</p></div><div class="stat-card"><p class="text-sm text-slate-500">Pendientes</p><p class="mt-2 text-3xl font-bold text-amber-600">{{ $pendientes }}</p></div><div class="stat-card"><p class="text-sm text-slate-500">En proceso</p><p class="mt-2 text-3xl font-bold text-teal-700">{{ $enProceso }}</p></div><div class="stat-card"><p class="text-sm text-slate-500">Finalizados</p><p class="mt-2 text-3xl font-bold text-blue-700">{{ $finalizados }}</p></div><div class="stat-card"><p class="text-sm text-slate-500">Cancelados</p><p class="mt-2 text-3xl font-bold text-rose-700">{{ $cancelados }}</p></div></div>
    <section class="panel mt-7 overflow-hidden"><div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-4">Cliente</th><th class="px-5 py-4">Abogado</th><th class="px-5 py-4">Especialidad</th><th class="px-5 py-4">Prioridad</th><th class="px-5 py-4">Estado</th><th class="px-5 py-4">Actualizar</th></tr></thead><tbody class="divide-y divide-slate-100 bg-white">
        @forelse($casos as $caso)
            @php
                $estado = app(App\Services\CaseWorkflowService::class)->normalizarEstadoCaso($caso->estado);
                $estadoClass = match($estado) {'En Proceso' => 'badge-progress', 'Finalizado' => 'badge-done', 'Cancelado' => 'badge-cancelled', default => 'badge-pending'};
                $prioridadClass = match($caso->prioridad) {'Alta' => 'badge-high', 'Baja' => 'badge-low', default => 'badge-medium'};
            @endphp
            <tr class="hover:bg-slate-50"><td class="px-5 py-4 font-semibold text-slate-900">{{ $caso->cliente }}</td><td class="px-5 py-4 text-slate-600">{{ $caso->abogado }}</td><td class="px-5 py-4 text-slate-600">{{ $caso->especialidad }}</td><td class="px-5 py-4"><span class="badge {{ $prioridadClass }}">{{ $caso->prioridad }}</span></td><td class="px-5 py-4"><span class="badge {{ $estadoClass }}">{{ $estado }}</span></td><td class="px-5 py-4"><form method="POST" action="{{ route('casos-admin.estado', $caso->id) }}" class="flex items-center gap-2">@csrf @method('PATCH')<select name="estado" class="rounded-lg border-slate-300 py-2 text-sm"><option value="Pendiente" @selected($estado === 'Pendiente')>Pendiente</option><option value="En Proceso" @selected($estado === 'En Proceso')>En Proceso</option><option value="Finalizado" @selected($estado === 'Finalizado')>Finalizado</option><option value="Cancelado" @selected($estado === 'Cancelado')>Cancelado</option></select><button type="submit" class="text-sm font-bold text-blue-700 hover:text-blue-800">Guardar</button></form></td></tr>
        @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">No hay casos registrados.</td></tr>
        @endforelse
    </tbody></table></div></section>
</div>
@endsection
