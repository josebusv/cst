<?php

namespace Database\Seeders;

use App\Models\ClasificacionBiomedica;
use Illuminate\Database\Seeder;

/**
 * Clasificaciones biomédicas por área/servicio clínico.
 *
 * Es una categoría funcional del equipo (no confundir con la clase de riesgo
 * INVIMA: I, IIa, IIb, III, que vive en la hoja de vida como `clase_riesgo`).
 */
class ClasificacionBiomedicaSeeder extends Seeder
{
    public function run(): void
    {
        $clasificaciones = [
            'Diagnóstico por imagen',
            'Monitoreo y signos vitales',
            'Soporte ventilatorio y anestesia',
            'Soporte cardiovascular y circulatorio',
            'Cuidado intensivo y reanimación',
            'Cardiología y diagnóstico cardiovascular',
            'Neurología y neurofisiología',
            'Oftalmología',
            'Otorrinolaringología',
            'Odontología y salud oral',
            'Gastroenterología y endoscopia',
            'Nefrología y diálisis',
            'Urología',
            'Ginecología y obstetricia',
            'Neonatología y cuidado neonatal',
            'Cirugía y sala de operaciones',
            'Esterilización y desinfección',
            'Laboratorio clínico',
            'Banco de sangre',
            'Anatomía patológica',
            'Radioterapia y medicina nuclear',
            'Rehabilitación y terapia física',
            'Terapia respiratoria',
            'Equipos de infusión y bombeo',
            'Emergencias y traslado de pacientes',
            'Mobiliario clínico y camas',
            'Instrumental médico y quirúrgico',
            'Medición y calibración biomédica',
            'Tecnología de la información clínica',
        ];

        foreach ($clasificaciones as $nombre) {
            ClasificacionBiomedica::firstOrCreate(
                ['nombre' => $nombre],
                ['activo' => true]
            );
        }
    }
}
