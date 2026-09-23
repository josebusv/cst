<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hojas_vida', function (Blueprint $table) {
            $table->string('otros_consumibles')->nullable()->after('accesorios');
        });
    }

    public function down(): void
    {
        Schema::table('hojas_vida', function (Blueprint $table) {
            $table->dropColumn('otros_consumibles');
        });
    }
};
