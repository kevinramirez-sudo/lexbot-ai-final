<x-guest-layout>
    <div class="mb-6">
        <p class="page-kicker">Acceso seguro</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900">Inicia sesión</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Accede a tu portal de LexBot AI.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <div><x-input-label for="email" value="Correo electrónico" /><x-text-input id="email" class="mt-2 block w-full rounded-xl" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" /><x-input-error :messages="$errors->get('email')" class="mt-2" /></div>
        <div><x-input-label for="password" value="Contraseña" /><x-text-input id="password" class="mt-2 block w-full rounded-xl" type="password" name="password" required autocomplete="current-password" /><x-input-error :messages="$errors->get('password')" class="mt-2" /></div>
        <label class="flex items-center gap-2 text-sm text-slate-600"><input id="remember_me" type="checkbox" class="rounded border-slate-300 text-blue-700 focus:ring-blue-600" name="remember"><span>Recordar sesión</span></label>
        <div class="flex flex-col gap-3 pt-1 sm:flex-row sm:items-center sm:justify-between">
            @if (Route::has('password.request'))<a class="text-sm font-semibold text-blue-700 hover:text-blue-800" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>@endif
            <x-primary-button class="justify-center rounded-xl !bg-blue-700 !px-5 !py-3 !text-sm !normal-case !tracking-normal hover:!bg-blue-800">Ingresar</x-primary-button>
        </div>
    </form>
    <p class="mt-6 border-t border-slate-100 pt-5 text-center text-sm text-slate-600">¿No tienes una cuenta? <a href="{{ route('register') }}" class="font-bold text-blue-700 hover:text-blue-800">Regístrate</a></p>
</x-guest-layout>
