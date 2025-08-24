<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained('sedes')->onDelete('cascade');
            $table->string('eqipo');
            $table->string('marca');
            $table->string('modelo');
            $table->string('serie')->unique();
            $table->string('fabricante')->nullable();
            $table->string('registro_invima')->nullable();
            $table->string('pais_origen')->nullable();
            $table->string('codigo')->nullable();
            $table->string('ubicacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipos');
    }
};
