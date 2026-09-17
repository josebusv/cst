<?php

namespace Tests\Feature\Api;

use App\Models\Empresa;
use App\Models\Equipo;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncidenciasTest extends TestCase
{
    use RefreshDatabase;

    private Equipo $equipo;
    private Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'api']);

        $this->empresa = Empresa::create(['nit' => '900000700', 'nombre' => 'Cliente X', 'tipo' => 'cliente']);
        $sede = Sede::create([
            'nombre' => 'Sede X', 'direccion' => 'Calle X', 'empresa_id' => $this->empresa->id,
            'departamento_id' => 1, 'municipio_id' => 1,
        ]);
        $this->equipo = Equipo::create([
            'sede_id' => $sede->id, 'equipo' => 'Equipo X', 'marca' => 'M', 'modelo' => 'Mo',
            'serie' => 'SX', 'fabricante' => 'F', 'servicio' => 'Endoscopia',
        ]);
    }

    private function actor(): User
    {
        $empresa = Empresa::create(['nit' => '900000701', 'nombre' => 'CST', 'tipo' => 'principal']);
        $sede = Sede::create([
            'nombre' => 'Sede Admin', 'direccion' => 'Calle 1', 'empresa_id' => $empresa->id,
            'departamento_id' => 1, 'municipio_id' => 1,
        ]);
        $user = User::factory()->create(['sede_id' => $sede->id, 'email' => 'admin.inc@test.dev']);
        $user->assignRole('Super-Admin');

        return $user;
    }

    public function test_bug01_mantenimiento_por_se_guarda_y_se_devuelve(): void
    {
        $actor = $this->actor();

        $this->actingAs($actor, 'api')
            ->putJson('/api/auth/equipos/' . $this->equipo->id . '/hoja-vida', [
                'mantenimiento_por' => 'BioServicios SAS',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('hojas_vida', [
            'equipo_id' => $this->equipo->id,
            'mantenimiento_por' => 'BioServicios SAS',
        ]);

        $this->actingAs($actor, 'api')
            ->getJson('/api/auth/equipos/' . $this->equipo->id . '/hoja-vida')
            ->assertStatus(200)
            ->assertJsonPath('data.mantenimiento_por', 'BioServicios SAS');
    }

    public function test_bug02_servicio_del_reporte_actualiza_el_equipo(): void
    {
        $actor = $this->actor();

        $payload = [
            'equipo_id' => $this->equipo->id,
            'tipo_reporte' => 'reporte_electronica',
            'servicio' => 'Gastroenterología',
            'fecha_reporte' => now()->toDateString(),
        ];
        for ($i = 1; $i <= 18; $i++) {
            $payload["chequeo$i"] = 'B';
        }

        $this->actingAs($actor, 'api')
            ->postJson('/api/auth/reportes', $payload)
            ->assertStatus(201);

        $this->assertDatabaseHas('equipos', [
            'id' => $this->equipo->id,
            'servicio' => 'Gastroenterología',
        ]);
    }

    public function test_bug03_y_bug04_cronograma_guarda_clase_riesgo_y_ubicacion(): void
    {
        $actor = $this->actor();

        $this->actingAs($actor, 'api')
            ->postJson('/api/auth/cronogramas', [
                'equipo_id' => $this->equipo->id,
                'year' => '2026',
                'month' => '05',
                'clase_riesgo' => 'clase_iia',
                'ubicacion' => 'Sala de procedimientos 3',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('cronogramas', [
            'equipo_id' => $this->equipo->id,
            'clase_riesgo' => 'clase_iia',
        ]);
        $this->assertDatabaseHas('equipos', [
            'id' => $this->equipo->id,
            'ubicacion' => 'Sala de procedimientos 3',
        ]);

        $this->actingAs($actor, 'api')
            ->getJson('/api/auth/cronogramas/empresa/' . $this->empresa->id . '/anual?year=2026')
            ->assertStatus(200)
            ->assertJsonPath('data.equipos.0.clase_riesgo', 'clase_iia')
            ->assertJsonPath('data.equipos.0.ubicacion', 'Sala de procedimientos 3');
    }
}
