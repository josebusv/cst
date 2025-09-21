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
        Schema::create('accesorio_equipo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->onDelete('cascade');
            $table->foreignId('accesorio_id')->constrained('accesorios');
            $table->string('nombre')->nullable();
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
        Schema::dropIfExists('accesorio_equipo');
    }
};
