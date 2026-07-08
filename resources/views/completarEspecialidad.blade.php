@extends('layouts.app')

@section('content')
<div class="page-container"><div class="mx-auto max-w-xl"><div class="mb-7"><p class="page-kicker">Perfil profesional</p><h1 class="page-title">Completa tu especialidad</h1><p class="page-subtitle">Antes de recibir casos debes indicar el área jurídica en la que atiendes.</p></div><section class="panel"><div class="panel-body"><form method="POST" action="{{ route('abogado.especialidad') }}" class="space-y-5">@csrf<label for="especialidad" class="block text-sm font-bold text-slate-800">Especialidad</label><select id="especialidad" name="especialidad" class="block w-full rounded-xl" required><option value="Civil">Civil</option><option value="Penal">Penal</option><option value="Familiar">Familiar</option><option value="Laboral">Laboral</option></select><button type="submit" class="btn-primary">Guardar especialidad</button></form></div></section></div></div>
@endsection
