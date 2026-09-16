<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Las firmas se guardan como data URLs base64 (PNG). En TEXT (máx 65.535 bytes)
 * pueden truncarse y quedar inválidas (imagen invisible). Se amplían a MEDIUMTEXT.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE reportes MODIFY firma_tecnico MEDIUMTEXT NULL');
        DB::statement('ALTER TABLE reportes MODIFY firma_cliente MEDIUMTEXT NULL');
        DB::statement('ALTER TABLE hojas_vida MODIFY firma_realizo MEDIUMTEXT NULL');
        DB::statement('ALTER TABLE hojas_vida MODIFY firma_aprobo MEDIUMTEXT NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE reportes MODIFY firma_tecnico TEXT NULL');
        DB::statement('ALTER TABLE reportes MODIFY firma_cliente TEXT NULL');
        DB::statement('ALTER TABLE hojas_vida MODIFY firma_realizo TEXT NULL');
        DB::statement('ALTER TABLE hojas_vida MODIFY firma_aprobo TEXT NULL');
    }
};
