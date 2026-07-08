@extends('layouts.app')

@section('content')
<div class="page-container">
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="page-kicker">Administración</p><h1 class="page-title">Abogados registrados</h1><p class="page-subtitle">Perfiles cargados directamente desde la base de datos.</p></div>
        <span class="badge border-blue-200 bg-blue-50 text-blue-700">{{ $totalAbogados }} abogados</span>
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse($abogados as $abogado)
            <article class="panel panel-body">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-lg font-bold text-blue-700">{{ strtoupper(mb_substr($abogado->nombre, 0, 1)) }}</div>
                    <span class="badge {{ $abogado->especialidad_visual === 'Sin asignar' ? 'border-slate-200 bg-slate-100 text-slate-600' : 'badge-progress' }}">{{ $abogado->especialidad_visual === 'Sin asignar' ? 'Sin asignar' : 'Disponible' }}</span>
                </div>
                <h2 class="mt-5 text-xl font-bold text-slate-900">{{ $abogado->nombre }}</h2>
                <p class="mt-1 break-all text-sm text-slate-600">{{ $abogado->correo }}</p>
                <dl class="mt-5 grid grid-cols-2 gap-4 border-t border-slate-100 pt-5"><div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Especialidad</dt><dd class="mt-1 font-bold text-slate-800">{{ $abogado->especialidad_visual }}</dd></div><div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Casos activos</dt><dd class="mt-1 font-bold text-slate-800">{{ $abogado->casos_activos }}</dd></div></dl>
            </article>
        @empty
            <div class="panel col-span-full p-10 text-center text-slate-500">No hay abogados registrados.</div>
        @endforelse
    </div>
</div>
@endsection
