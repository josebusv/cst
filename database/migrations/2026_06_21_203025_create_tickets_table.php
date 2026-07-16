<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('equipo_id')->nullable()->constrained('equipos')->onDelete('set null');
            $table->foreignId('tecnico_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('titulo');
            $table->text('descripcion');
            $table->enum('estado', ['abierto', 'en_progreso', 'cerrado', 'cancelado'])->default('abierto');
            $table->enum('prioridad', ['alta', 'media', 'baja'])->default('media');
            $table->dateTime('fecha_cierre')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
