<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sede;
use App\Models\Empresa;

class SedeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sede::factory()->count(1)->create([
            'nombre' => 'Unica',
            'empresa_id' => Empresa::first()->id, // Assign a random empresa_id
            'departamento_id' => 1, // Example department ID
            'municipio_id' => 1, // Example municipality ID
        ]);

        Sede::factory()->count(10)->create();

    }
}
