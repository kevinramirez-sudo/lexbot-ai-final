@extends('layouts.app')

@section('content')
<div class="page-container">
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="page-kicker">Administración</p><h1 class="page-title">Clientes registrados</h1><p class="page-subtitle">Usuarios con rol cliente y su actividad acumulada.</p></div><span class="badge border-violet-200 bg-violet-50 text-violet-700">{{ $totalClientes }} clientes</span></div>

    <section class="panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500"><tr><th class="px-6 py-4">Cliente</th><th class="px-6 py-4">Correo</th><th class="px-6 py-4 text-center">Casos</th><th class="px-6 py-4 text-center">Citas</th><th class="px-6 py-4">Estado</th></tr></thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($clientes as $cliente)
                        <tr class="hover:bg-slate-50"><td class="px-6 py-4"><div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-full bg-violet-100 font-bold text-violet-700">{{ strtoupper(mb_substr($cliente->nombre, 0, 1)) }}</span><span class="font-bold text-slate-900">{{ $cliente->nombre }}</span></div></td><td class="px-6 py-4 text-slate-600">{{ $cliente->email }}</td><td class="px-6 py-4 text-center font-bold text-slate-800">{{ $cliente->total_casos }}</td><td class="px-6 py-4 text-center font-bold text-slate-800">{{ $cliente->total_citas }}</td><td class="px-6 py-4"><span class="badge badge-progress">Activo</span></td></tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">No existen clientes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
