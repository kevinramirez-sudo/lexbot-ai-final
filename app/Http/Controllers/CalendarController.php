<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $rol = $request->user()->rol;

        return view('calendario', [
            'rol' => $rol,
            'puedeEditar' => $rol === 'abogado',
        ]);
    }
}
