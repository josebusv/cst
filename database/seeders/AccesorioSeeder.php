<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Accesorio;

class AccesorioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Accesorio::factory()->count(20)->create();
    }
}
