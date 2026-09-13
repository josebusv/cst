<?php

namespace Tests\Feature\Api;

use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Municipio;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ImportacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_importa_filas_validas_y_reporta_las_invalidas(): void
    {
        Departamento::create(['nombre' => 'Cundinamarca', 'codigo' => 'CUN']);
        $departamento = Departamento::query()->first();
        Municipio::create(['nombre' => 'Madrid', 'departamento_id' => $departamento->id, 'codigo' => 'CUN001']);

        $headers = ['cliente_nombre', 'cliente_nit', 'sede_nombre', 'sede_departamento', 'sede_municipio', 'sede_direccion', 'sede_telefono', 'equipo', 'marca', 'modelo', 'serie'];
        $rows = [
            ['CLIENTE A', '111', 'CENTRAL', 'Cundinamarca', 'Madrid', 'CR 1 #2-3', '3001112233', 'EQUIPO X', 'Marca', 'Modelo', 'SN001'],
            ['', '222', 'SIN CLIENTE', '', '', '', '', '', '', '', ''],
        ];
        $csv = implode("\n", array_map(fn ($row) => implode(';', $row), array_merge([$headers], $rows)));

        $file = UploadedFile::fake()->createWithContent('import.csv', $csv);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken())
            ->post('/api/auth/importar', ['archivo' => $file]);

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertSame(1, $data['total']);
        $this->assertSame(1, $data['clientes_creados']);
        $this->assertSame(1, $data['equipos_creados']);
        $this->assertNotEmpty($data['errores']);
        $this->assertSame(1, Empresa::where('tipo', 'cliente')->count());
    }

    private function adminToken(): string
    {
        Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'api']);

        $empresa = Empresa::create(['nit' => '900000099', 'nombre' => 'Principal', 'tipo' => 'principal']);
        $sede = Sede::create([
            'nombre' => 'Sede Admin',
            'direccion' => 'Calle 1',
            'empresa_id' => $empresa->id,
            'departamento_id' => 1,
            'municipio_id' => 1,
        ]);

        $user = User::factory()->create([
            'sede_id' => $sede->id,
            'email' => 'admin.import@test.dev',
            'password' => bcrypt('secret123'),
        ]);
        $user->assignRole('Super-Admin');

        return $this->postJson('/api/auth/login', [
            'email' => 'admin.import@test.dev',
            'password' => 'secret123',
        ])->json('access_token');
    }
}
