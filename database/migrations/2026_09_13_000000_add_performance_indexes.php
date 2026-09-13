<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->index('tipo', 'empresas_tipo_index');
        });

        Schema::table('cronogramas', function (Blueprint $table) {
            $table->index(['year', 'month'], 'cronogramas_year_month_index');
            $table->index('estado', 'cronogramas_estado_index');
            $table->index('fecha_programada', 'cronogramas_fecha_programada_index');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->index(['empresa_id', 'created_at'], 'tickets_empresa_created_index');
            $table->index('estado', 'tickets_estado_index');
        });

        Schema::table('reportes', function (Blueprint $table) {
            $table->index(['equipo_id', 'created_at'], 'reportes_equipo_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropIndex('empresas_tipo_index');
        });

        Schema::table('cronogramas', function (Blueprint $table) {
            $table->dropIndex('cronogramas_year_month_index');
            $table->dropIndex('cronogramas_estado_index');
            $table->dropIndex('cronogramas_fecha_programada_index');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('tickets_empresa_created_index');
            $table->dropIndex('tickets_estado_index');
        });

        Schema::table('reportes', function (Blueprint $table) {
            $table->dropIndex('reportes_equipo_created_index');
        });
    }
};
