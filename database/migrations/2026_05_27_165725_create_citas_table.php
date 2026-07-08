<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('citas')) {
            return;
        }

        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('caso_id')->nullable()->index();
            $table->string('cliente');
            $table->string('cliente_email')->nullable()->index();
            $table->string('abogado');
            $table->string('especialidad');
            $table->date('fecha');
            $table->time('hora');
            $table->text('motivo');
            $table->string('estado')->default('pendiente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
