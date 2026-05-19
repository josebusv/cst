<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->text('normal')->nullable()->after('preventivo');
            $table->text('irregular')->nullable()->after('normal');
        });

        Schema::table('reportes', function (Blueprint $table) {
            $table->renameColumn('fuerea_servicio', 'fuera_servicio');
        });
    }

    public function down()
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->renameColumn('fuera_servicio', 'fuerea_servicio');
            $table->dropColumn(['normal', 'irregular']);
        });
    }
};