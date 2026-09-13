<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE empresas MODIFY nit VARCHAR(20) NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE empresas MODIFY nit INT NULL');
    }
};
