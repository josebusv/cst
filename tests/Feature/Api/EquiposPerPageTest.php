<?php

namespace Tests\Feature\Api;

use App\Models\Empresa;
use App\Models\Equipo;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EquiposPerPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_per_page_se_respeta_con_tope_de_cien(): void
    {
        $token = $this->adminToken();
        [$cliente, $sede] = $this->clienteConEquipos(3);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/equipos/empresa/' . $cliente->id . '?per_page=1000');

        $response->assertStatus(200);
        $this->assertSame(100, $response->json('meta.per_page'));
        $this->assertCount(3, $response->json('data'));
    }

    public function test_per_page_limita_los_resultados(): void
    {
        $token = $this->adminToken();
        [$cliente] = $this->clienteConEquipos(3);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/equipos/empresa/' . $cliente->id . '?per_page=2');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $this->assertSame(2, $response->json('meta.last_page'));
    }

    private function clienteConEquipos(int $cantidad): array
    {
        $cliente = Empresa::create(['nit' => '900000200', 'nombre' => 'Cliente Equipos', 'tipo' => 'cliente']);
        $sede = Sede::create([
            'nombre' => 'Sede Equipos',
            'direccion' => 'Calle 9',
            'empresa_id' => $cliente->id,
            'departamento_id' => 1,
            'municipio_id' => 1,
        ]);

        for ($i = 1; $i <= $cantidad; $i++) {
            Equipo::create([
                'sede_id' => $sede->id,
                'equipo' => 'Equipo ' . $i,
                'marca' => 'Marca',
                'modelo' => 'Modelo',
                'serie' => 'SERIE-' . $i,
                'servicio' => 'Servicio',
                'fabricante' => 'Fabricante',
            ]);
        }

        return [$cliente, $sede];
    }

    private function adminToken(): string
    {
        Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'api']);

        $empresa = Empresa::create(['nit' => '900000199', 'nombre' => 'Principal', 'tipo' => 'principal']);
        $sede = Sede::create([
            'nombre' => 'Sede Admin',
            'direccion' => 'Calle 1',
            'empresa_id' => $empresa->id,
            'departamento_id' => 1,
            'municipio_id' => 1,
        ]);

        $user = User::factory()->create([
            'sede_id' => $sede->id,
            'email' => 'admin.equipos@test.dev',
            'password' => bcrypt('secret123'),
        ]);
        $user->assignRole('Super-Admin');

        return $this->postJson('/api/auth/login', [
            'email' => 'admin.equipos@test.dev',
            'password' => 'secret123',
        ])->json('access_token');
    }
}
