<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('hojas_vida', function (Blueprint $table) {
            $table->string('nombre_realizo')->nullable()->after('firma_realizo_user_id');
            $table->string('nombre_aprobo')->nullable()->after('firma_aprobo_user_id');
        });
    }

    public function down()
    {
        Schema::table('hojas_vida', function (Blueprint $table) {
            $table->dropColumn(['nombre_realizo', 'nombre_aprobo']);
        });
    }
};
