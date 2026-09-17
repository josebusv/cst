<?php

namespace Tests\Feature\Api;

use App\Models\Empresa;
use App\Models\Equipo;
use App\Models\Reporte;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MultiTenantAccessTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresaA;
    private Empresa $empresaB;
    private Equipo $equipoA;
    private Equipo $equipoB;
    private Reporte $reporteA;
    private Reporte $reporteB;
    private User $userA;

    protected function setUp(): void
    {
        parent::setUp();

        $permisos = [
            'Listar Equipos', 'Ver Hoja De Vida', 'Listar Reportes', 'Ver Tickets',
            'Listar Sedes', 'Listar Usuarios', 'Ver Clientes', 'Firmar Reportes',
            'Asignar Operadores',
        ];
        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'api']);
        }

        $rolCliente = Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'api']);
        $rolCliente->syncPermissions([
            'Listar Equipos', 'Ver Hoja De Vida', 'Listar Reportes', 'Ver Tickets',
            'Listar Sedes', 'Listar Usuarios', 'Ver Clientes', 'Firmar Reportes',
        ]);

        $this->empresaA = Empresa::create(['nit' => '900000001', 'nombre' => 'Cliente A', 'tipo' => 'cliente']);
        $this->empresaB = Empresa::create(['nit' => '900000002', 'nombre' => 'Cliente B', 'tipo' => 'cliente']);

        $sedeA = Sede::create([
            'nombre' => 'Sede A', 'direccion' => 'Calle A', 'empresa_id' => $this->empresaA->id,
            'departamento_id' => 1, 'municipio_id' => 1,
        ]);
        $sedeB = Sede::create([
            'nombre' => 'Sede B', 'direccion' => 'Calle B', 'empresa_id' => $this->empresaB->id,
            'departamento_id' => 1, 'municipio_id' => 1,
        ]);

        $this->equipoA = Equipo::create([
            'sede_id' => $sedeA->id, 'equipo' => 'Equipo A', 'marca' => 'Marca',
            'modelo' => 'Modelo', 'serie' => 'SERIE-A', 'fabricante' => 'Fab', 'servicio' => 'Endoscopia',
        ]);
        $this->equipoB = Equipo::create([
            'sede_id' => $sedeB->id, 'equipo' => 'Equipo B', 'marca' => 'Marca',
            'modelo' => 'Modelo', 'serie' => 'SERIE-B', 'fabricante' => 'Fab', 'servicio' => 'Endoscopia',
        ]);

        $this->reporteA = Reporte::create([
            'equipo_id' => $this->equipoA->id, 'tipo_reporte' => 'reporte_electronica',
            'fecha_reporte' => now()->toDateString(),
        ]);
        $this->reporteB = Reporte::create([
            'equipo_id' => $this->equipoB->id, 'tipo_reporte' => 'reporte_electronica',
            'fecha_reporte' => now()->toDateString(),
        ]);

        $this->userA = User::factory()->create([
            'sede_id' => $sedeA->id, 'email' => 'cliente.a@test.dev', 'password' => bcrypt('secret123'),
        ]);
        $this->userA->assignRole('Cliente');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_cliente_no_puede_ver_equipo_de_otra_empresa(): void
    {
        $this->conAuth($this->userA)
            ->getJson('/api/auth/equipos/' . $this->equipoB->id)
            ->assertStatus(403);
    }

    public function test_cliente_si_puede_ver_equipo_de_su_empresa(): void
    {
        $this->conAuth($this->userA)
            ->getJson('/api/auth/equipos/' . $this->equipoA->id)
            ->assertStatus(200);
    }

    public function test_cliente_no_puede_listar_equipos_de_otra_empresa(): void
    {
        $this->conAuth($this->userA)
            ->getJson('/api/auth/equipos/empresa/' . $this->empresaB->id)
            ->assertStatus(403);
    }

    public function test_cliente_solo_lista_sus_equipos(): void
    {
        $res = $this->conAuth($this->userA)->getJson('/api/auth/equipos')->assertStatus(200);

        $ids = collect($res->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($this->equipoA->id));
        $this->assertFalse($ids->contains($this->equipoB->id));
    }

    public function test_cliente_no_puede_firmar_reporte_de_otra_empresa(): void
    {
        $this->conAuth($this->userA)
            ->patchJson('/api/auth/reportes/' . $this->reporteB->id . '/firma-tecnico', [
                'firma_tecnico' => 'data:image/png;base64,AAAA',
            ])
            ->assertStatus(403);
    }

    public function test_cliente_si_puede_firmar_reporte_de_su_empresa(): void
    {
        $this->conAuth($this->userA)
            ->patchJson('/api/auth/reportes/' . $this->reporteA->id . '/firma-tecnico', [
                'firma_tecnico' => 'data:image/png;base64,AAAA',
            ])
            ->assertStatus(200);
    }

    public function test_usuario_sin_permiso_no_puede_firmar_reporte(): void
    {
        $rol = Role::firstOrCreate(['name' => 'SoloLectura', 'guard_name' => 'api']);
        $rol->syncPermissions(['Listar Reportes']);

        $user = User::factory()->create([
            'sede_id' => $this->userA->sede_id, 'email' => 'lectura@test.dev', 'password' => bcrypt('secret123'),
        ]);
        $user->assignRole('SoloLectura');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->conAuth($user)
            ->patchJson('/api/auth/reportes/' . $this->reporteA->id . '/firma-tecnico', [
                'firma_tecnico' => 'data:image/png;base64,AAAA',
            ])
            ->assertStatus(403);
    }

    public function test_cliente_no_puede_asignar_tecnicos(): void
    {
        $this->conAuth($this->userA)
            ->postJson('/api/auth/clientes/' . $this->empresaA->id . '/tecnicos', [
                'user_id' => $this->userA->id,
            ])
            ->assertStatus(403);
    }

    public function test_cliente_no_puede_listar_usuarios_de_otra_empresa(): void
    {
        $this->conAuth($this->userA)
            ->getJson('/api/auth/users/empresa/' . $this->empresaB->id)
            ->assertStatus(403);
    }

    private function conAuth(User $user): self
    {
        $this->actingAs($user, 'api');

        return $this;
    }
}
