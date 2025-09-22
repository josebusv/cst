<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sede;
use App\Models\Empresa;
use App\Models\Equipo;
use App\Models\Accesorio;


class SedeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sedes = Sede::all();

        foreach ($sedes as $sede) {
            $equipos = Equipo::factory()->count(15)->create([
                    'sede_id' => $sede->id,
                ]);
                foreach ($equipos as $equipo) {
                    $equipo->accesorios()->attach(
                        Accesorio::inRandomOrder()->take(rand(1, 3))->pluck('id')
                    );
                }
        }
    }
}
