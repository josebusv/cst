<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cronogramas', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'completado', 'vencido'])->default('pendiente')->after('reporte_id');
            $table->enum('periodicidad', ['mensual', 'bimestral', 'trimestral', 'semestral', 'anual'])->nullable()->after('estado');
            $table->date('fecha_programada')->nullable()->after('periodicidad');
            $table->date('fecha_ejecucion')->nullable()->after('fecha_programada');
            $table->foreignId('tecnico_id')->nullable()->constrained('users')->onDelete('set null')->after('fecha_ejecucion');
            $table->text('observaciones')->nullable()->after('tecnico_id');

            $table->foreign('clasificacion_biomedica_id')
                ->references('id')
                ->on('clasificaciones_biomedicas')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('cronogramas', function (Blueprint $table) {
            $table->dropForeign(['clasificacion_biomedica_id']);
            $table->dropForeign(['tecnico_id']);
            $table->dropColumn(['estado', 'periodicidad', 'fecha_programada', 'fecha_ejecucion', 'tecnico_id', 'observaciones']);
        });
    }
};
