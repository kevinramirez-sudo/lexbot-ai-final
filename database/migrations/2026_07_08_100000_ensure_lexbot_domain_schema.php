<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('especialidades')) {
            Schema::create('especialidades', function (Blueprint $table) {
                $table->id();
                $table->string('nombre')->unique();
            });
        }

        if (!Schema::hasTable('abogados')) {
            Schema::create('abogados', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->string('correo')->unique();
                $table->string('especialidad')->nullable()->default('Sin asignar');
                $table->unsignedBigInteger('especialidad_id')->nullable();
            });
        }

        if (!Schema::hasTable('casos')) {
            Schema::create('casos', function (Blueprint $table) {
                $table->id();
                $table->string('cliente');
                $table->string('cliente_email')->nullable()->index();
                $table->string('abogado');
                $table->string('especialidad');
                $table->text('descripcion');
                $table->string('prioridad')->default('Media');
                $table->string('estado')->default('Pendiente');
                $table->timestamp('fecha_creacion')->useCurrent();
            });
        }

        if (!Schema::hasTable('mensajes')) {
            Schema::create('mensajes', function (Blueprint $table) {
                $table->id();
                $table->string('nombre_cliente')->nullable();
                $table->string('correo_cliente')->nullable();
                $table->string('canal')->nullable();
                $table->text('mensaje')->nullable();
                $table->string('categoria')->nullable();
                $table->string('prioridad')->nullable();
                $table->text('respuesta_ia')->nullable();
                $table->text('resumen')->nullable();
                $table->string('abogado_asignado')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        $this->agregarColumnaSiNoExiste('casos', 'cliente_email', function (Blueprint $table) {
            $table->string('cliente_email')->nullable()->index();
        });

        $this->agregarColumnaSiNoExiste('citas', 'caso_id', function (Blueprint $table) {
            $table->unsignedBigInteger('caso_id')->nullable()->index();
        });
        $this->agregarColumnaSiNoExiste('citas', 'cliente', function (Blueprint $table) {
            $table->string('cliente')->nullable();
        });
        $this->agregarColumnaSiNoExiste('citas', 'cliente_email', function (Blueprint $table) {
            $table->string('cliente_email')->nullable()->index();
        });
        $this->agregarColumnaSiNoExiste('citas', 'abogado', function (Blueprint $table) {
            $table->string('abogado')->nullable();
        });
        $this->agregarColumnaSiNoExiste('citas', 'especialidad', function (Blueprint $table) {
            $table->string('especialidad')->nullable();
        });
        $this->agregarColumnaSiNoExiste('citas', 'hora', function (Blueprint $table) {
            $table->time('hora')->nullable();
        });
        $this->agregarColumnaSiNoExiste('citas', 'motivo', function (Blueprint $table) {
            $table->text('motivo')->nullable();
        });

        $this->agregarColumnaSiNoExiste('notificaciones', 'caso_id', function (Blueprint $table) {
            $table->unsignedBigInteger('caso_id')->nullable()->index();
        });
        $this->agregarColumnaSiNoExiste('notificaciones', 'cliente_email', function (Blueprint $table) {
            $table->string('cliente_email')->nullable()->index();
        });
    }

    public function down(): void
    {
        // This migration only completes schemas that may already contain data.
        // It is intentionally not destructive.
    }

    private function agregarColumnaSiNoExiste(string $tabla, string $columna, Closure $definicion): void
    {
        if (Schema::hasTable($tabla) && !Schema::hasColumn($tabla, $columna)) {
            Schema::table($tabla, $definicion);
        }
    }
};
