<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('unidades_tecnicas', function (Blueprint $table) {
            $table->id();
            $table->string('categoria'); // voltaje, corriente, potencia, etc.
            $table->string('nombre');     // V, A, W, Hz, PSI, RPM, L, Kg, °C, mm
            $table->string('simbolo')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['categoria', 'nombre']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('unidades_tecnicas');
    }
};
