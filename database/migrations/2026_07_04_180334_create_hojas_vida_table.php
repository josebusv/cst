<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hojas_vida', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->unique()->constrained('equipos')->cascadeOnDelete();

            // Propietario responsable
            $table->string('contacto_responsable')->nullable();
            $table->string('telefono_responsable')->nullable();

            // Secciones JSON
            $table->json('especificaciones_tecnicas')->nullable();
            $table->json('fuentes_alimentacion')->nullable();
            $table->json('sistemas_consulta')->nullable();

            // Clasificación
            $table->enum('uso', ['diagnostico', 'tratamiento', 'laboratorio', 'rehabilitacion', 'esterilizacion', 'otro'])->nullable();
            $table->enum('tipo_dispositivo', ['activo', 'activo_terapeutico', 'combinado', 'dm_implantable', 'dm_invasivo', 'dm_invasivo_qx'])->nullable();
            $table->enum('clase_riesgo', ['clase_i', 'clase_iia', 'clase_iib', 'clase_iii'])->nullable();

            // Firmas (igual que reportes)
            $table->foreignId('firma_realizo_user_id')->nullable()->constrained('users');
            $table->text('firma_realizo')->nullable();
            $table->string('cargo_realizo')->nullable();
            $table->foreignId('firma_aprobo_user_id')->nullable()->constrained('users');
            $table->text('firma_aprobo')->nullable();
            $table->string('cargo_aprobo')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hojas_vida');
    }
};
