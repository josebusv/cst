<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Municipio;

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
        $municipio = Municipio::inRandomOrder()->first();
        return [
            'nombre' => $this->faker->companySuffix . ' Sede',
            'direccion' => $this->faker->address,
            'telefono' => $this->faker->phoneNumber,
            'empresa_id' => \App\Models\Empresa::all()->random()->id, // Assuming Empresa model exists and has records
            'email' => $this->faker->unique()->safeEmail,
            'activo' => $this->faker->boolean,
            'departamento_id' => $municipio->departamento_id,
            'municipio_id' => $municipio->id,
            'created_by' => null,
            'updated_by' => null,
            'deleted_by' => null,
        ];
    }
}
