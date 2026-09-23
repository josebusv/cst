<?php

namespace Tests\Feature\Api;

use App\Models\Empresa;
use App\Models\Equipo;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiContractTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;
    private Equipo $equipo;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'api']);

        $empresa = Empresa::create(['nit' => '900000900', 'nombre' => 'CST', 'tipo' => 'principal']);
        $sede = Sede::create([
            'nombre' => 'Sede', 'direccion' => 'C', 'empresa_id' => $empresa->id,
            'departamento_id' => 1, 'municipio_id' => 1,
        ]);
        $this->actor = User::factory()->create(['sede_id' => $sede->id, 'email' => 'admin.contract@test.dev']);
        $this->actor->assignRole('Super-Admin');

        $this->equipo = Equipo::create([
            'sede_id' => $sede->id, 'equipo' => 'Eq 1', 'marca' => 'M', 'modelo' => 'Mo',
            'serie' => 'S1', 'fabricante' => 'F', 'servicio' => 'Endoscopia',
        ]);
        foreach (['S2', 'S3'] as $serie) {
            Equipo::create([
                'sede_id' => $sede->id, 'equipo' => 'Eq ' . $serie, 'marca' => 'M', 'modelo' => 'Mo',
                'serie' => $serie, 'fabricante' => 'F', 'servicio' => 'Endoscopia',
            ]);
        }
    }

    public function test_error_401_incluye_code(): void
    {
        $this->getJson('/api/auth/dashboard/admin')
            ->assertStatus(401)
            ->assertJsonPath('code', 'UNAUTHENTICATED')
            ->assertJsonStructure(['message', 'code', 'error']);
    }

    public function test_error_422_incluye_code(): void
    {
        $this->postJson('/api/auth/forgot-password', ['email' => 'no-es-email'])
            ->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['message', 'code', 'errors']);
    }

    public function test_error_404_incluye_code(): void
    {
        $this->actingAs($this->actor, 'api')
            ->getJson('/api/auth/equipos/999999')
            ->assertStatus(404)
            ->assertJsonPath('code', 'NOT_FOUND')
            // No debe filtrar el nombre interno del modelo.
            ->assertJsonPath('error', null);
    }

    public function test_per_page_se_respeta_y_se_acota(): void
    {
        $res = $this->actingAs($this->actor, 'api')
            ->getJson('/api/auth/equipos?per_page=2')
            ->assertStatus(200);

        $this->assertCount(2, $res->json('data'));
        $this->assertSame(2, $res->json('meta.per_page'));

        $res2 = $this->actingAs($this->actor, 'api')
            ->getJson('/api/auth/equipos?per_page=1000')
            ->assertStatus(200);

        $this->assertSame(100, $res2->json('meta.per_page'));
    }

    public function test_hoja_vida_detalle_devuelve_campos_esperados(): void
    {
        $this->actingAs($this->actor, 'api')
            ->putJson('/api/auth/equipos/' . $this->equipo->id . '/hoja-vida', [
                'contacto_responsable' => 'Juan',
            ])
            ->assertStatus(200);

        $this->actingAs($this->actor, 'api')
            ->getJson('/api/auth/equipos/' . $this->equipo->id . '/hoja-vida-detalle')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [
                'id', 'equipo_id', 'mantenimiento_por', 'contacto_responsable',
                'especificaciones_tecnicas', 'sistemas_consulta', 'accesorios',
                'otros_consumibles', 'clase_riesgo', 'firma_realizo', 'firma_aprobo',
            ]])
            ->assertJsonPath('data.contacto_responsable', 'Juan');
    }

    public function test_alias_importaciones_existe(): void
    {
        $this->actingAs($this->actor, 'api')
            ->postJson('/api/auth/importaciones', [])
            ->assertStatus(422);
    }

    public function test_alias_generacion_existe(): void
    {
        $this->actingAs($this->actor, 'api')
            ->postJson('/api/auth/cronogramas/generacion', [])
            ->assertStatus(422);
    }
}
