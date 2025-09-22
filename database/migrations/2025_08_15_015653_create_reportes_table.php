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
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('equipo_id');
            $table->string('tipo_reporte', 20);
            $table->text('correctivo')->nullable();
            $table->text('preventivo')->nullable();
            $table->text('fuerea_servicio')->nullable();
            $table->text('requerido_cliente')->nullable();
            $table->text('falla_reportada')->nullable();
            $table->enum('chequeo1',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo2',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo3',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo4',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo5',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo6',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo7',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo8',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo9',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo10',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo11',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo12',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo13',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo14',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo15',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo16',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo17',['B', 'R', 'M', 'NA'])->nullable();
            $table->enum('chequeo18',['B', 'R', 'M', 'NA'])->nullable();
            $table->boolean('limpieza_interna')->default(false);
            $table->boolean('limpieza_externa')->default(false);
            $table->boolean('lubricacion')->default(false);
            $table->boolean('ajuste_angulacion')->default(false);
            $table->boolean('prueba_fugas')->default(false);
            $table->boolean('ajuste_general')->default(false);
            $table->boolean('cable_paciente')->default(false);
            $table->boolean('verificacion_software')->default(false);
            $table->boolean('filtros')->default(false);
            $table->boolean('verificacion_general')->default(false);
            $table->boolean('reemplazo_insumo')->default(false);
            $table->boolean('baterias')->default(false);
            $table->text('servicio_realizado')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('firma_tecnico')->nullable();
            $table->text('nombre_prestador')->nullable();
            $table->text('cargo_prestador')->nullable();
            $table->text('firma_cliente')->nullable();
            $table->text('nombre_cliente')->nullable();
            $table->text('cargo_cliente')->nullable();
            $table->date('fecha_reporte');
            $table->softDeletes();
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
        Schema::dropIfExists('reportes');
    }
};
