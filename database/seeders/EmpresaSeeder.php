<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Empresa;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Empresa::factory()->count(1)->create([
            'tipo' => 'principal', // Specific type for principal companies
        ]);

        Empresa::factory()->count(10)->create([
            'tipo' => 'cliente', // Default type for all created companies
        ]);
        
    }
}
