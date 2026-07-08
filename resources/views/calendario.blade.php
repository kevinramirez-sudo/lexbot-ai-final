@extends('layouts.app')

@section('content')
<div class="page-container">
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="page-kicker">Agenda jurídica</p><h1 class="page-title">Calendario de citas</h1><p class="page-subtitle">@if($rol === 'abogado') Puedes arrastrar tus propias citas para reprogramarlas. @elseif($rol === 'admin') Visualiza la agenda completa de la firma. @else Consulta únicamente tus citas. @endif</p></div></div>
    <div class="mb-6 grid gap-3 sm:grid-cols-4"><div class="panel p-4 text-sm font-semibold text-slate-700"><span class="mr-2 inline-block h-3 w-3 rounded-full bg-amber-500"></span>Pendiente</div><div class="panel p-4 text-sm font-semibold text-slate-700"><span class="mr-2 inline-block h-3 w-3 rounded-full bg-teal-700"></span>Confirmada</div><div class="panel p-4 text-sm font-semibold text-slate-700"><span class="mr-2 inline-block h-3 w-3 rounded-full bg-blue-600"></span>Finalizada</div><div class="panel p-4 text-sm font-semibold text-slate-700"><span class="mr-2 inline-block h-3 w-3 rounded-full bg-rose-600"></span>Cancelada</div></div>
    <section class="panel p-3 sm:p-6"><div id="calendar"></div></section>
</div>

<div id="modalDetalle" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-4"><div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl"><div class="flex items-center justify-between"><h2 class="text-xl font-bold text-slate-900">Detalle de la cita</h2><button id="cerrarModal" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" type="button">✕</button></div><dl class="mt-5 grid gap-4 text-sm"><div><dt class="font-bold text-slate-500">Cliente</dt><dd id="detalleCliente" class="mt-1 text-slate-900"></dd></div><div><dt class="font-bold text-slate-500">Abogado</dt><dd id="detalleAbogado" class="mt-1 text-slate-900"></dd></div><div><dt class="font-bold text-slate-500">Especialidad</dt><dd id="detalleEspecialidad" class="mt-1 text-slate-900"></dd></div><div class="grid grid-cols-2 gap-4"><div><dt class="font-bold text-slate-500">Fecha</dt><dd id="detalleFecha" class="mt-1 text-slate-900"></dd></div><div><dt class="font-bold text-slate-500">Hora</dt><dd id="detalleHora" class="mt-1 text-slate-900"></dd></div></div><div><dt class="font-bold text-slate-500">Estado</dt><dd id="detalleEstado" class="mt-1 text-slate-900"></dd></div><div><dt class="font-bold text-slate-500">Motivo</dt><dd id="detalleMotivo" class="mt-1 whitespace-pre-line rounded-xl bg-slate-50 p-4 leading-6 text-slate-700"></dd></div></dl></div></div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales-all.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('calendar');
    const modal = document.getElementById('modalDetalle');
    const puedeEditar = {{ $puedeEditar ? 'true' : 'false' }};
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es', timeZone: 'America/Guayaquil', initialView: 'dayGridMonth', height: 'auto', firstDay: 1, nowIndicator: true,
        editable: puedeEditar, eventStartEditable: puedeEditar, eventDurationEditable: false,
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek' },
        buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana' },
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false }, events: '{{ route('citas.obtener') }}',
        eventClick(info) { const d = info.event.extendedProps; document.getElementById('detalleCliente').textContent = d.cliente; document.getElementById('detalleAbogado').textContent = d.abogado; document.getElementById('detalleEspecialidad').textContent = d.especialidad; document.getElementById('detalleFecha').textContent = info.event.startStr.substring(0, 10); document.getElementById('detalleHora').textContent = d.hora; document.getElementById('detalleEstado').textContent = d.estado; document.getElementById('detalleMotivo').textContent = d.motivo; modal.classList.remove('hidden'); modal.classList.add('flex'); },
        async eventDrop(info) { try { const fecha = info.event.startStr.substring(0, 10); const hora = info.event.startStr.includes('T') ? info.event.startStr.substring(11, 16) : info.event.extendedProps.hora; const response = await fetch('{{ url('/citas') }}/' + info.event.id, { method: 'PUT', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'}, body: JSON.stringify({fecha, hora}) }); const data = await response.json().catch(()=>({success:false,mensaje:'Respuesta inválida'})); if(!response.ok || !data.success) throw new Error(data.mensaje || 'No se pudo actualizar la cita.'); info.event.setExtendedProp('hora', data.hora); alert(data.mensaje); } catch (error) { info.revert(); alert(error.message || 'No se pudo actualizar la cita.'); } }
    });
    calendar.render();
    const cerrar = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); };
    document.getElementById('cerrarModal').addEventListener('click', cerrar); modal.addEventListener('click', e => { if (e.target === modal) cerrar(); });
});
</script>
@endsection
