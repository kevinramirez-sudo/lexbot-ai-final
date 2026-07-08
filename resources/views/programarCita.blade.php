@extends('layouts.app')

@section('content')
<div class="page-container">
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="page-kicker">Agenda profesional</p><h1 class="page-title">Programar cita</h1><p class="page-subtitle">Cliente: <strong>{{ $caso->cliente }}</strong> · Caso de {{ $caso->especialidad }}.</p></div><a href="{{ route('abogado.caso.ver', $caso->id) }}" class="btn-secondary">Volver al caso</a></div>
    @if(session('error'))<div class="alert-error">{{ session('error') }}</div>@endif
    <section class="panel max-w-2xl"><div class="panel-body"><form method="POST" action="{{ route('abogado.cita.guardar', $caso->id) }}" class="space-y-5">@csrf<div><label for="fecha" class="mb-2 block text-sm font-bold text-slate-800">Fecha</label><input id="fecha" name="fecha" type="date" min="{{ now('America/Guayaquil')->format('Y-m-d') }}" value="{{ old('fecha') }}" required class="block w-full rounded-xl"></div><div><label for="hora" class="mb-2 block text-sm font-bold text-slate-800">Hora</label><input id="hora" name="hora" type="time" min="09:00" max="17:00" value="{{ old('hora') }}" required class="block w-full rounded-xl"></div><div class="rounded-xl bg-blue-50 p-4 text-sm leading-6 text-blue-800">El sistema valida que el horario no esté ocupado y notifica al cliente en su portal y Telegram.</div><button type="submit" class="btn-primary">Guardar cita</button></form></div></section>
</div>
@endsection
