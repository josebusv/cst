<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sede>
 */
class SedeFactory extends Factory
{

    protected $model = \App\Models\Sede::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->companySuffix . ' Sede',
            'direccion' => $this->faker->address,
            'telefono' => $this->faker->phoneNumber,
            'empresa_id' => \App\Models\Empresa::all()->random()->id, // Assuming Empresa model exists and has records
            'email' => $this->faker->unique()->safeEmail,
            'activo' => $this->faker->boolean,
            'departamento_id' => $this->faker->numberBetween(1, 10), // Assuming valid departamento IDs
            'municipio_id' => $this->faker->numberBetween(1, 100), // Assuming valid municipio IDs
            'created_by' => null,
            'updated_by' => null,
            'deleted_by' => null,
        ];
    }
}
