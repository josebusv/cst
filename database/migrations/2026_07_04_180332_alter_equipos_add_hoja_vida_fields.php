<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->string('imagen')->nullable()->after('code_ecri');
            $table->foreignId('tipo_equipo_id')->nullable()->after('imagen')->constrained('tipos_equipos');
            $table->foreignId('clasificacion_biomedica_id')->nullable()->after('tipo_equipo_id')->constrained('clasificaciones_biomedicas');
            $table->enum('estado', ['operativo', 'mantenimiento', 'inoperativo', 'fuera_servicio'])->default('operativo')->after('clasificacion_biomedica_id');
            $table->date('fecha_adquisicion')->nullable()->after('estado');
            $table->date('fecha_instalacion')->nullable()->after('fecha_adquisicion');
            $table->integer('garantia_meses')->nullable()->after('fecha_instalacion');
            $table->integer('vida_util_meses')->nullable()->after('garantia_meses');
        });
    }

    public function down()
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropForeign(['tipo_equipo_id']);
            $table->dropForeign(['clasificacion_biomedica_id']);
            $table->dropColumn([
                'imagen',
                'tipo_equipo_id',
                'clasificacion_biomedica_id',
                'estado',
                'fecha_adquisicion',
                'fecha_instalacion',
                'garantia_meses',
                'vida_util_meses',
            ]);
        });
    }
};
