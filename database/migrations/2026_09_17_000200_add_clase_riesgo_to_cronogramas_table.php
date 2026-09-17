<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cronogramas', function (Blueprint $table) {
            $table->enum('clase_riesgo', ['clase_i', 'clase_iia', 'clase_iib', 'clase_iii'])
                ->nullable()
                ->after('clasificacion_biomedica_id');
        });

        // La clasificacion biomedica (area clinica) pasa a ser opcional: la
        // clasificacion regulatoria del cronograma es la clase de riesgo.
        Schema::table('cronogramas', function (Blueprint $table) {
            $table->unsignedBigInteger('clasificacion_biomedica_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cronogramas', function (Blueprint $table) {
            $table->dropColumn('clase_riesgo');
        });
    }
};
