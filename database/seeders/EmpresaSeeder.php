<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Empresa;
use App\Models\Municipio;
use App\Models\Sede;
use App\Models\User;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $principal = Empresa::factory()->count(1)->create([
            'tipo' => 'principal', // Specific type for principal companies
        ]);


        $sede = Sede::factory()->create([
            'empresa_id' => $principal->first()->id,
            'principal' => true,
        ]);

        User::factory(1)->create([
            'name' => 'Jose Luis Bustos valencia',
            'email' => 'josebusv@gmail.com',
            'password' => bcrypt('12345678'),
            'sede_id' => 1,
            'is_active' => 1
        ]);

        $clientes = Empresa::factory()->count(100)->create([
            'tipo' => 'cliente', // Default type for all created companies
        ]);

        foreach ($clientes as $cliente) {
            $municipio = Municipio::inRandomOrder()->first();

            $sede = Sede::factory()->create([
                'empresa_id' => $cliente->id,
                'principal' => true,
            ]);

            User::factory(5)->create([
                'sede_id' => $sede->id,
                'is_active' => 1
            ]);
        }
        
    }
}
