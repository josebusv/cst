<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoEquipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipoEquipos = [
            'Videocolonoscopio',
            'Videogastroscopio',
            'Videoduodenoscopio',
            'Videoenteroscopio',
            'Eco endoscopio lineal',
            'Eco endoscopio radial',
            'Videobroncoscopio',
            'Videocostoscopio',
            'Lente rígido',
            'Procesador de video',
            'Fuente de luz',
            'Consola de Ecoendoscopia',
            'Monitor de video',
            'Unidad de control de balón',
            'Cabezal de cámara',
            'Succionador',
            'Unidad de mantenimiento',
            'Probador de fugas',
            'Arco en c',
            'Ecografo',
            'Camilla de transporte',
            'Monitor de signos vitales',
            'Desfibrilador',
            'Báscula',
            'Equipo de órganos',
            'Tensiometro',
            'Tallimentro',
            'Camilla',
            'Electrobistury',
            'Unidad de argón',
            'Bomba de irrigación',
            'Bomba de CO2',
            'Neumo insuflador',
            'Regulador de oxígeno',
            'Reprocesador'
        ];

        foreach ($tipoEquipos as $nombre) {
            \App\Models\TipoEquipo::create(['tipo' => $nombre]);
        }
    }
}
