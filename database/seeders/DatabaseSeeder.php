<?php

namespace Database\Seeders;

use App\Models\Abogado;
use App\Models\Especialidad;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Civil', 'Penal', 'Familiar', 'Laboral'] as $especialidad) {
            Especialidad::firstOrCreate([
                'nombre' => $especialidad,
            ]);
        }

        User::firstOrCreate(
            ['email' => 'admin@lexbot.local'],
            [
                'nombre' => 'Administrador LexBot',
                'password' => Hash::make('Lexbot2026!'),
                'rol' => 'admin',
            ]
        );

        $abogados = [
            ['nombre' => 'Andrea Penal', 'correo' => 'andrea.penal@lexbot.local', 'especialidad' => 'Penal'],
            ['nombre' => 'Bruno Familiar', 'correo' => 'bruno.familiar@lexbot.local', 'especialidad' => 'Familiar'],
            ['nombre' => 'Carla Laboral', 'correo' => 'carla.laboral@lexbot.local', 'especialidad' => 'Laboral'],
            ['nombre' => 'Diego Civil', 'correo' => 'diego.civil@lexbot.local', 'especialidad' => 'Civil'],
        ];

        foreach ($abogados as $datos) {
            User::firstOrCreate(
                ['email' => $datos['correo']],
                [
                    'nombre' => $datos['nombre'],
                    'password' => Hash::make('Lexbot2026!'),
                    'rol' => 'abogado',
                ]
            );

            Abogado::firstOrCreate(
                ['correo' => $datos['correo']],
                [
                    'nombre' => $datos['nombre'],
                    'especialidad' => $datos['especialidad'],
                ]
            );
        }

        User::firstOrCreate(
            ['email' => 'cliente@lexbot.local'],
            [
                'nombre' => 'Cliente Demo',
                'password' => Hash::make('Lexbot2026!'),
                'rol' => 'cliente',
            ]
        );
    }
}
