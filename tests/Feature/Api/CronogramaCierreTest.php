<?php

namespace Tests\Feature\Api;

use App\Models\Cronograma;
use App\Models\Empresa;
use App\Models\Equipo;
use App\Models\Reporte;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CronogramaCierreTest extends TestCase
{
    use RefreshDatabase;

    private Equipo $equipo;
    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'api']);

        $principal = Empresa::create(['nit' => '900000800', 'nombre' => 'CST', 'tipo' => 'principal']);
        $sedeAdmin = Sede::create([
            'nombre' => 'Admin', 'direccion' => 'C', 'empresa_id' => $principal->id,
            'departamento_id' => 1, 'municipio_id' => 1,
        ]);
        $this->actor = User::factory()->create(['sede_id' => $sedeAdmin->id, 'email' => 'admin.cierre@test.dev']);
        $this->actor->assignRole('Super-Admin');

        $empresa = Empresa::create(['nit' => '900000801', 'nombre' => 'Cliente Y', 'tipo' => 'cliente']);
        $sede = Sede::create([
            'nombre' => 'Sede Y', 'direccion' => 'C', 'empresa_id' => $empresa->id,
            'departamento_id' => 1, 'municipio_id' => 1,
        ]);
        $this->equipo = Equipo::create([
            'sede_id' => $sede->id, 'equipo' => 'Equipo Y', 'marca' => 'M', 'modelo' => 'Mo',
            'serie' => 'SY', 'fabricante' => 'F', 'servicio' => 'Endoscopia',
        ]);
    }

    private function cronograma(): Cronograma
    {
        return Cronograma::create([
            'equipo_id' => $this->equipo->id,
            'year' => '2026',
            'month' => '06',
            'estado' => 'pendiente',
        ]);
    }

    private function payloadReporte(array $extra = []): array
    {
        $payload = [
            'equipo_id' => $this->equipo->id,
            'tipo_reporte' => 'reporte_electronica',
            'fecha_reporte' => now()->toDateString(),
        ];
        for ($i = 1; $i <= 18; $i++) {
            $payload["chequeo$i"] = 'B';
        }

        return array_merge($payload, $extra);
    }

    public function test_crear_reporte_con_cronograma_lo_marca_completado(): void
    {
        $cronograma = $this->cronograma();

        $this->actingAs($this->actor, 'api')
            ->postJson('/api/auth/reportes', $this->payloadReporte(['cronograma_id' => $cronograma->id]))
            ->assertStatus(201);

        $cronograma->refresh();
        $this->assertSame('completado', $cronograma->estado);
        $this->assertNotNull($cronograma->reporte_id);
        $this->assertNotNull($cronograma->fecha_ejecucion);
    }

    public function test_no_se_puede_completar_cronograma_sin_reporte(): void
    {
        $cronograma = $this->cronograma();

        $this->actingAs($this->actor, 'api')
            ->putJson('/api/auth/cronogramas/' . $cronograma->id, ['estado' => 'completado'])
            ->assertStatus(422);

        $this->assertSame('pendiente', $cronograma->fresh()->estado);
    }

    public function test_vincular_reporte_completa_el_cronograma(): void
    {
        $cronograma = $this->cronograma();
        $reporte = Reporte::create([
            'equipo_id' => $this->equipo->id,
            'tipo_reporte' => 'reporte_electronica',
            'fecha_reporte' => now()->toDateString(),
        ]);

        $this->actingAs($this->actor, 'api')
            ->putJson('/api/auth/cronogramas/' . $cronograma->id, ['reporte_id' => $reporte->id])
            ->assertStatus(200);

        $cronograma->refresh();
        $this->assertSame('completado', $cronograma->estado);
        $this->assertSame($reporte->id, $cronograma->reporte_id);
        $this->assertNotNull($cronograma->fecha_ejecucion);
    }
}
