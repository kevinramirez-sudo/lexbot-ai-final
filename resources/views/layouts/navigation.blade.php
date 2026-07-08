@php
    $rol = auth()->user()?->rol;
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-7">
            <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-700 text-sm font-bold text-white shadow-sm">L</span>
                <span class="hidden sm:block">
                    <span class="block text-sm font-bold leading-4 text-slate-900">LexBot AI</span>
                    <span class="block text-[11px] leading-4 text-slate-500">Gestión jurídica</span>
                </span>
            </a>

            <div class="hidden items-center gap-1 md:flex">
                @if($rol === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} rounded-lg px-3 py-2 text-sm font-semibold">Inicio</a>
                    <a href="{{ route('admin.clientes') }}" class="{{ request()->routeIs('admin.clientes') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} rounded-lg px-3 py-2 text-sm font-semibold">Clientes</a>
                    <a href="{{ route('admin.abogados') }}" class="{{ request()->routeIs('admin.abogados') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} rounded-lg px-3 py-2 text-sm font-semibold">Abogados</a>
                    <a href="{{ route('casos-admin') }}" class="{{ request()->routeIs('casos-admin*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} rounded-lg px-3 py-2 text-sm font-semibold">Casos</a>
                    <a href="{{ route('calendario') }}" class="{{ request()->routeIs('calendario') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} rounded-lg px-3 py-2 text-sm font-semibold">Calendario</a>
                @elseif($rol === 'abogado')
                    <a href="{{ route('abogado.dashboard') }}" class="{{ request()->routeIs('abogado.dashboard') || request()->routeIs('abogado.caso.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} rounded-lg px-3 py-2 text-sm font-semibold">Mi panel</a>
                    <a href="{{ route('calendario') }}" class="{{ request()->routeIs('calendario') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} rounded-lg px-3 py-2 text-sm font-semibold">Calendario</a>
                @else
                    <a href="{{ route('cliente.portal') }}" class="{{ request()->routeIs('cliente.portal') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} rounded-lg px-3 py-2 text-sm font-semibold">Mi portal</a>
                    <a href="{{ route('calendario') }}" class="{{ request()->routeIs('calendario') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} rounded-lg px-3 py-2 text-sm font-semibold">Calendario</a>
                @endif
            </div>
        </div>

        <div class="hidden items-center gap-3 md:flex">
            <div class="text-right">
                <p class="max-w-40 truncate text-sm font-semibold text-slate-800">{{ auth()->user()?->nombre }}</p>
                <p class="text-xs text-slate-500">{{ ucfirst($rol ?? '') }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-secondary !px-3 !py-2">Salir</button>
            </form>
        </div>

        <button @click="open = !open" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 md:hidden" aria-label="Abrir menú">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
    </div>

    <div x-show="open" x-cloak class="border-t border-slate-200 bg-white md:hidden">
        <div class="space-y-1 px-4 py-3">
            @if($rol === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Inicio</a>
                <a href="{{ route('admin.clientes') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Clientes</a>
                <a href="{{ route('admin.abogados') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Abogados</a>
                <a href="{{ route('casos-admin') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Casos</a>
            @elseif($rol === 'abogado')
                <a href="{{ route('abogado.dashboard') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Mi panel</a>
            @else
                <a href="{{ route('cliente.portal') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Mi portal</a>
            @endif
            <a href="{{ route('calendario') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Calendario</a>
            <form method="POST" action="{{ route('logout') }}" class="pt-2">
                @csrf
                <button type="submit" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-left text-sm font-semibold text-slate-700">Cerrar sesión</button>
            </form>
        </div>
    </div>
</nav>
