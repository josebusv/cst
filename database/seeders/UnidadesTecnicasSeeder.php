<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnidadTecnica;

class UnidadesTecnicasSeeder extends Seeder
{
    public function run()
    {
        $unidades = [
            ['categoria' => 'voltaje', 'nombre' => 'V', 'simbolo' => 'V'],
            ['categoria' => 'voltaje', 'nombre' => 'kV', 'simbolo' => 'kV'],
            ['categoria' => 'corriente', 'nombre' => 'A', 'simbolo' => 'A'],
            ['categoria' => 'corriente', 'nombre' => 'mA', 'simbolo' => 'mA'],
            ['categoria' => 'potencia', 'nombre' => 'W', 'simbolo' => 'W'],
            ['categoria' => 'potencia', 'nombre' => 'kW', 'simbolo' => 'kW'],
            ['categoria' => 'potencia', 'nombre' => 'HP', 'simbolo' => 'HP'],
            ['categoria' => 'frecuencia', 'nombre' => 'Hz', 'simbolo' => 'Hz'],
            ['categoria' => 'frecuencia', 'nombre' => 'kHz', 'simbolo' => 'kHz'],
            ['categoria' => 'presion', 'nombre' => 'PSI', 'simbolo' => 'PSI'],
            ['categoria' => 'presion', 'nombre' => 'Bar', 'simbolo' => 'bar'],
            ['categoria' => 'presion', 'nombre' => 'kPa', 'simbolo' => 'kPa'],
            ['categoria' => 'velocidad', 'nombre' => 'RPM', 'simbolo' => 'RPM'],
            ['categoria' => 'velocidad', 'nombre' => 'Hz', 'simbolo' => 'Hz'],
            ['categoria' => 'capacidad', 'nombre' => 'L', 'simbolo' => 'L'],
            ['categoria' => 'capacidad', 'nombre' => 'mL', 'simbolo' => 'mL'],
            ['categoria' => 'capacidad', 'nombre' => 'm³', 'simbolo' => 'm³'],
            ['categoria' => 'peso', 'nombre' => 'Kg', 'simbolo' => 'kg'],
            ['categoria' => 'peso', 'nombre' => 'g', 'simbolo' => 'g'],
            ['categoria' => 'peso', 'nombre' => 'lb', 'simbolo' => 'lb'],
            ['categoria' => 'temperatura', 'nombre' => '°C', 'simbolo' => '°C'],
            ['categoria' => 'temperatura', 'nombre' => '°F', 'simbolo' => '°F'],
            ['categoria' => 'temperatura', 'nombre' => 'K', 'simbolo' => 'K'],
            ['categoria' => 'dimensiones', 'nombre' => 'mm', 'simbolo' => 'mm'],
            ['categoria' => 'dimensiones', 'nombre' => 'cm', 'simbolo' => 'cm'],
            ['categoria' => 'dimensiones', 'nombre' => 'm', 'simbolo' => 'm'],
        ];

        foreach ($unidades as $u) {
            UnidadTecnica::firstOrCreate(
                ['categoria' => $u['categoria'], 'nombre' => $u['nombre']],
                $u
            );
        }
    }
}
