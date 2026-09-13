<?php

namespace Tests\Feature\Api;

use App\Models\ClasificacionBiomedica;
use App\Models\Cronograma;
use App\Models\Empresa;
use App\Models\Equipo;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CronogramaAnualTest extends TestCase
{
    use RefreshDatabase;

    public function test_devuelve_equipos_con_ubicacion_clasificacion_y_cronogramas(): void
    {
        $cliente = Empresa::create(['nit' => '900000400', 'nombre' => 'Cliente Anual', 'tipo' => 'cliente']);
        $sede = Sede::create([
            'nombre' => 'Sede Anual',
            'direccion' => 'Calle 1',
            'empresa_id' => $cliente->id,
            'departamento_id' => 1,
            'municipio_id' => 1,
        ]);
        $clasificacion = ClasificacionBiomedica::create(['nombre' => 'Diagnóstico por imagen', 'activo' => true]);

        $equipo = Equipo::create([
            'sede_id' => $sede->id,
            'equipo' => 'Ecógrafo',
            'marca' => 'Philips',
            'modelo' => 'EPIQ',
            'serie' => 'SER-1',
            'servicio' => 'Imágenes',
            'fabricante' => 'Philips',
            'clasificacion_biomedica_id' => $clasificacion->id,
        ]);

        Cronograma::create([
            'equipo_id' => $equipo->id,
            'year' => '2026',
            'month' => '06',
            'clasificacion_biomedica_id' => $clasificacion->id,
            'estado' => 'completado',
            'periodicidad' => 'semestral',
            'tipo' => 'mantenimiento',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken())
            ->getJson('/api/auth/cronogramas/empresa/' . $cliente->id . '/anual?year=2026');

        $response->assertStatus(200)
            ->assertJsonPath('data.year', '2026')
            ->assertJsonPath('data.equipos.0.equipo', 'Ecógrafo')
            ->assertJsonPath('data.equipos.0.clasificacion_biomedica', 'Diagnóstico por imagen')
            ->assertJsonPath('data.equipos.0.cronogramas.0.month', '06')
            ->assertJsonPath('data.equipos.0.cronogramas.0.tipo', 'mantenimiento');
    }

    private function adminToken(): string
    {
        Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'api']);

        $empresa = Empresa::create(['nit' => '900000401', 'nombre' => 'Principal', 'tipo' => 'principal']);
        $sede = Sede::create([
            'nombre' => 'Sede Admin',
            'direccion' => 'Calle 1',
            'empresa_id' => $empresa->id,
            'departamento_id' => 1,
            'municipio_id' => 1,
        ]);

        $user = User::factory()->create([
            'sede_id' => $sede->id,
            'email' => 'admin.anual@test.dev',
            'password' => bcrypt('secret123'),
        ]);
        $user->assignRole('Super-Admin');

        return $this->postJson('/api/auth/login', [
            'email' => 'admin.anual@test.dev',
            'password' => 'secret123',
        ])->json('access_token');
    }
}
