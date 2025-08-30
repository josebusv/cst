<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(1)->create([
            'name' => 'Jose Luis Bustos valencia',
            'email' => 'josebusv@gmail.com',
            'password' => bcrypt('12345678'),
            'sede_id' => 1,
            'is_active' => 1
        ]);

        User::factory(1000)->create();
    }
}
