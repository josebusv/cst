<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Consumible;

class ConsumiblesSeeder extends Seeder
{
    public function run()
    {
        $consumibles = [
            'Filtros',
            'Microfiltro',
            'Electrodos',
            'Papel',
            'Bombillos',
        ];

        foreach ($consumibles as $nombre) {
            Consumible::firstOrCreate(['nombre' => $nombre], ['descripcion' => null]);
        }
    }
}
