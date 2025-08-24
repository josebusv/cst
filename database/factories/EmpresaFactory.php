<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empresa>
 */
class EmpresaFactory extends Factory
{

    protected $model = \App\Models\Empresa::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nit' => $this->faker->unique()->numerify('#########'),
            'nombre' => $this->faker->company,
            'logo' => $this->faker->imageUrl(640, 480, 'business', true, 'Faker'),
            'tipo' => 'cliente',
        ];
    }
}
