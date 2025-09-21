<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Equipo;

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
            'sede_id' => $this->faker->randomNumber(),
            'equipo' => $this->faker->word(),
            'marca' => $this->faker->word(),
            'modelo' => $this->faker->word(),
            'serie' => $this->faker->word(),
            'fabricante' => $this->faker->word(),
            'registro_invima' => $this->faker->word(),
            'pais_origen' => $this->faker->country(),
            'ubicacion' => $this->faker->word(),
        ];
    }
}
