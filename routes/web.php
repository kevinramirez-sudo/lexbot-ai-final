<?php

use App\Http\Controllers\AbogadoController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CasoAdminController;
use App\Http\Controllers\CasoController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\LawyerController;
use App\Http\Controllers\LexBotController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    return match (Auth::user()->rol) {
        'admin' => redirect()->route('admin.dashboard'),
        'abogado' => redirect()->route('abogado.dashboard'),
        default => redirect()->route('cliente.portal'),
    };
})->name('home');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])
        ->name('admin.dashboard');
    Route::get('/clientes', [ClienteController::class, 'index'])
        ->name('admin.clientes');
    Route::get('/abogados', [AbogadoController::class, 'index'])
        ->name('admin.abogados');
    Route::get('/casos-admin', [CasoAdminController::class, 'index'])
        ->name('casos-admin');
    Route::patch('/casos-admin/{id}/estado', [CasoAdminController::class, 'actualizarEstado'])
        ->name('casos-admin.estado');
});

Route::middleware(['auth', 'role:cliente'])->group(function () {
    Route::get('/cliente', [ClientController::class, 'index'])
        ->name('cliente.portal');
    Route::post('/analizar-caso', [CasoController::class, 'analizar'])
        ->name('casos.analizar');
    Route::post('/casos', [CasoController::class, 'guardar'])
        ->name('casos.guardar');
    Route::get('/nuevo-caso', fn () => redirect()->route('cliente.portal'))
        ->name('casos.nuevo');
});

Route::middleware(['auth', 'role:abogado'])->group(function () {
    Route::get('/abogado', [LawyerController::class, 'index'])
        ->name('abogado.dashboard');
    Route::get('/caso/{id}', [LawyerController::class, 'verCaso'])
        ->name('abogado.caso.ver');
    Route::post('/caso/{id}/estado', [LawyerController::class, 'actualizarEstado'])
        ->name('abogado.caso.estado');
    Route::get('/caso/{id}/cita', [LawyerController::class, 'formularioCita'])
        ->name('abogado.cita.formulario');
    Route::post('/caso/{id}/cita', [LawyerController::class, 'guardarCita'])
        ->name('abogado.cita.guardar');
    Route::post('/guardar-especialidad', [LawyerController::class, 'actualizarEspecialidad'])
        ->name('abogado.especialidad');
});

Route::middleware('auth')->group(function () {
    Route::get('/calendario', [CalendarController::class, 'index'])
        ->name('calendario');
    Route::get('/citas', [CitaController::class, 'obtener'])
        ->name('citas.obtener');
    Route::put('/citas/{id}', [CitaController::class, 'actualizar'])
        ->name('citas.actualizar');

    Route::get('/dashboard', function () {
        return match (Auth::user()->rol) {
            'admin' => redirect()->route('admin.dashboard'),
            'abogado' => redirect()->route('abogado.dashboard'),
            default => redirect()->route('cliente.portal'),
        };
    })->name('dashboard');
});

Route::post('/lexbot', [LexBotController::class, 'analizar'])
    ->name('telegram.webhook');

require __DIR__.'/auth.php';
