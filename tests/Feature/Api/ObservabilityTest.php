<?php

namespace Tests\Feature\Api;

use App\Models\Empresa;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ObservabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_las_respuestas_incluyen_x_request_id(): void
    {
        $response = $this->getJson('/api/auth/dashboard/admin');

        $response->assertHeader('X-Request-Id');
        $this->assertNotEmpty($response->headers->get('X-Request-Id'));
    }

    public function test_se_respeta_el_x_request_id_enviado(): void
    {
        $response = $this->withHeader('X-Request-Id', 'abc-123')
            ->getJson('/api/auth/dashboard/admin');

        $response->assertHeader('X-Request-Id', 'abc-123');
    }

    public function test_endpoint_de_log_de_cliente_requiere_autenticacion(): void
    {
        $this->postJson('/api/auth/logs/client', ['message' => 'boom'])
            ->assertStatus(401);
    }

    public function test_endpoint_de_log_de_cliente_acepta_errores_autenticado(): void
    {
        $token = $this->adminToken();

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logs/client', [
                'message' => 'TypeError: x is undefined',
                'url' => '/admin/equipos',
                'stack' => 'at foo (main.js:1)',
            ])
            ->assertStatus(200)
            ->assertJson(['ok' => true]);
    }

    public function test_el_canal_webhook_envia_la_alerta(): void
    {
        Http::fake();
        config(['logging.channels.webhook.handler_with.url' => 'https://alerts.test/hook']);

        Log::channel('webhook')->error('fallo de prueba');

        Http::assertSent(fn ($request) => $request->url() === 'https://alerts.test/hook'
            && $request['message'] === 'fallo de prueba');
    }

    public function test_el_canal_telegram_envia_la_alerta(): void
    {
        Http::fake();
        config([
            'logging.channels.telegram.handler_with.botToken' => 'TOKEN',
            'logging.channels.telegram.handler_with.chatId' => '12345',
        ]);

        Log::channel('telegram')->error('fallo de prueba');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.telegram.org/botTOKEN/sendMessage')
            && $request['chat_id'] === '12345');
    }

    private function adminToken(): string
    {
        Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'api']);

        $empresa = Empresa::create(['nit' => '900000300', 'nombre' => 'Principal', 'tipo' => 'principal']);
        $sede = Sede::create([
            'nombre' => 'Sede Admin',
            'direccion' => 'Calle 1',
            'empresa_id' => $empresa->id,
            'departamento_id' => 1,
            'municipio_id' => 1,
        ]);

        $user = User::factory()->create([
            'sede_id' => $sede->id,
            'email' => 'admin.obs@test.dev',
            'password' => bcrypt('secret123'),
        ]);
        $user->assignRole('Super-Admin');

        return $this->postJson('/api/auth/login', [
            'email' => 'admin.obs@test.dev',
            'password' => 'secret123',
        ])->json('access_token');
    }
}
