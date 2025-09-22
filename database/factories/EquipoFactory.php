<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Equipo;
use App\Models\Sede;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipo>
 */
class EquipoFactory extends Factory
{

    protected $model = Equipo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sede_id' => Sede::inRandomOrder()->take(rand(1, 3))->pluck('id')->first(),
            'equipo' => $this->faker->word(),
            'marca' => $this->faker->word(),
            'modelo' => $this->faker->word(),
            'serie' => $this->faker->word(),
            'servicio' => 'Endoscopia',
            'fabricante' => $this->faker->word(),
            'registro_invima' => $this->faker->word(),
            'pais_origen' => $this->faker->country(),
            'ubicacion' => $this->faker->word(),
        ];
    }
}
