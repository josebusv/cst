<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Accesorio;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accesorio>
 */
class AccesorioFactory extends Factory
{
    protected $model = Accesorio::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->word(),
            'descripcion' => $this->faker->sentence(),
        ];
    }
}
