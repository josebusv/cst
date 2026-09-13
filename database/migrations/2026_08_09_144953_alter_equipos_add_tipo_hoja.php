<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->enum('tipo_hoja', ['electronica', 'endoscopia'])
                ->default('electronica')
                ->after('clasificacion_biomedica_id');
        });
    }

    public function down()
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn('tipo_hoja');
        });
    }
};
