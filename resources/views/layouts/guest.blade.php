<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'LexBot AI') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100">
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 px-4 py-10 sm:py-16">
        <div class="mx-auto flex w-full max-w-md flex-col items-center">
            <a href="{{ route('home') }}" class="mb-7 flex items-center gap-3 text-white">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-xl font-bold shadow-lg">L</span>
                <span>
                    <span class="block text-lg font-bold">LexBot AI</span>
                    <span class="block text-xs text-blue-200">Gestión jurídica inteligente</span>
                </span>
            </a>

            <div class="w-full rounded-3xl border border-white/10 bg-white p-6 shadow-2xl sm:p-8">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
