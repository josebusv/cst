<?php

namespace Tests\Feature\Api;

use App\Models\Empresa;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SecurityPhase3Test extends TestCase
{
    use RefreshDatabase;

    private Sede $sede;
    private User $admin;
    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Crear Usuarios', 'Editar Usuarios', 'Eliminar Usuarios', 'Listar Usuarios'] as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'api']);
        }

        $admin = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'api']);
        $admin->syncPermissions(['Crear Usuarios', 'Editar Usuarios', 'Eliminar Usuarios', 'Listar Usuarios']);
        Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'Operador', 'guard_name' => 'api']);

        $empresa = Empresa::create(['nit' => '900000900', 'nombre' => 'CST', 'tipo' => 'principal']);
        $this->sede = Sede::create([
            'nombre' => 'Sede Principal', 'direccion' => 'Calle 1', 'empresa_id' => $empresa->id,
            'departamento_id' => 1, 'municipio_id' => 1,
        ]);

        $this->admin = User::factory()->create(['sede_id' => $this->sede->id, 'email' => 'admin.p3@test.dev']);
        $this->admin->assignRole('Administrador');

        $this->superAdmin = User::factory()->create(['sede_id' => $this->sede->id, 'email' => 'root.p3@test.dev']);
        $this->superAdmin->assignRole('Super-Admin');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_password_debil_es_rechazada(): void
    {
        $this->conAuth($this->admin)
            ->postJson('/api/auth/users', [
                'name' => 'Nuevo', 'email' => 'nuevo.p3@test.dev',
                'password' => 'abc', 'password_confirmation' => 'abc',
                'role_id' => Role::findByName('Operador', 'api')->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    public function test_password_fuerte_es_aceptada(): void
    {
        $this->conAuth($this->admin)
            ->postJson('/api/auth/users', [
                'name' => 'Nuevo', 'email' => 'nuevo.p3@test.dev',
                'password' => 'Clave1234', 'password_confirmation' => 'Clave1234',
                'sede_id' => $this->sede->id,
                'role_id' => Role::findByName('Operador', 'api')->id,
            ])
            ->assertStatus(201);
    }

    public function test_admin_no_puede_asignar_rol_super_admin(): void
    {
        $operador = User::factory()->create(['sede_id' => $this->sede->id, 'email' => 'oper.p3@test.dev']);
        $operador->assignRole('Operador');

        $this->conAuth($this->admin)
            ->patchJson('/api/auth/users/' . $operador->id, [
                'role_id' => Role::findByName('Super-Admin', 'api')->id,
            ])
            ->assertStatus(403);
    }

    public function test_no_puede_eliminar_su_propia_cuenta(): void
    {
        $this->conAuth($this->admin)
            ->deleteJson('/api/auth/users/' . $this->admin->id)
            ->assertStatus(403);
    }

    public function test_no_puede_eliminar_al_ultimo_super_admin(): void
    {
        $this->conAuth($this->admin)
            ->deleteJson('/api/auth/users/' . $this->superAdmin->id)
            ->assertStatus(403);
    }

    public function test_login_se_bloquea_tras_cinco_intentos(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', ['email' => 'brute.p3@test.dev', 'password' => 'malo']);
        }

        $this->postJson('/api/auth/login', ['email' => 'brute.p3@test.dev', 'password' => 'malo'])
            ->assertStatus(429);
    }

    private function conAuth(User $user): self
    {
        $this->actingAs($user, 'api');

        return $this;
    }
}
