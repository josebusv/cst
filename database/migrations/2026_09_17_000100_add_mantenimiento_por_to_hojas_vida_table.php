<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hojas_vida', function (Blueprint $table) {
            $table->string('mantenimiento_por')->nullable()->after('equipo_id');
        });
    }

    public function down(): void
    {
        Schema::table('hojas_vida', function (Blueprint $table) {
            $table->dropColumn('mantenimiento_por');
        });
    }
};
