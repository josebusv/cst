<?php

namespace Tests\Feature\Api;

use App\Models\Empresa;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthAndAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_protected_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/auth/dashboard/admin')->assertStatus(401);
    }

    public function test_login_with_invalid_credentials_returns_401(): void
    {
        $this->postJson('/api/auth/login', [
            'email' => 'noexiste@example.com',
            'password' => 'incorrecta',
        ])->assertStatus(401);
    }

    public function test_unknown_api_route_returns_json_404(): void
    {
        $this->getJson('/api/ruta-que-no-existe')
            ->assertStatus(404)
            ->assertJson(['message' => 'Ruta no encontrada']);
    }

    public function test_user_can_login_and_access_me(): void
    {
        [$empresa, $sede, $user] = $this->crearUsuario();

        $login = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertStatus(200)->json();

        $this->assertArrayHasKey('access_token', $login);

        $this->withHeader('Authorization', 'Bearer ' . $login['access_token'])
            ->postJson('/api/auth/me')
            ->assertStatus(200)
            ->assertJson(['email' => $user->email]);
    }

    private function crearUsuario(): array
    {
        $empresa = Empresa::create([
            'nit' => '900000001',
            'nombre' => 'Empresa Test',
            'tipo' => 'principal',
        ]);

        $sede = Sede::create([
            'nombre' => 'Sede Test',
            'direccion' => 'Calle 1 # 2-3',
            'empresa_id' => $empresa->id,
            'departamento_id' => 1,
            'municipio_id' => 1,
        ]);

        $user = User::factory()->create([
            'sede_id' => $sede->id,
            'email' => 'test@example.com',
            'password' => bcrypt('secret123'),
        ]);

        return [$empresa, $sede, $user];
    }
}
