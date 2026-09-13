<?php

namespace Database\Seeders;

use App\Models\Accesorio;
use App\Models\ClasificacionBiomedica;
use App\Models\Consumible;
use App\Models\Cronograma;
use App\Models\Empresa;
use App\Models\Equipo;
use App\Models\Municipio;
use App\Models\Sede;
use App\Models\Ticket;
use App\Models\TipoEquipo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Datos de demostración idempotentes para validar los flujos de la app.
 *
 * Credenciales:
 *   demo.admin@cst.test   / demo12345   (Super-Admin)
 *   demo.cliente@cst.test / demo12345   (Cliente)
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissionsDemoSeeder::class);

        $municipio = Municipio::query()->first();
        if (!$municipio) {
            $this->call([DepartamentoSeeder::class, MunicipioSeeder::class]);
            $municipio = Municipio::query()->first();
        }

        $principal = Empresa::firstOrCreate(
            ['nit' => '900000000-1'],
            ['nombre' => 'CST (Demo principal)', 'tipo' => 'principal']
        );

        $sedePrincipal = Sede::firstOrCreate(
            ['empresa_id' => $principal->id, 'nombre' => 'Sede Principal Demo'],
            [
                'direccion' => 'Calle Demo 123',
                'telefono' => '3000000000',
                'email' => 'demo@cst.test',
                'departamento_id' => $municipio->departamento_id,
                'municipio_id' => $municipio->id,
                'principal' => true,
                'activo' => true,
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'demo.admin@cst.test'],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make('demo12345'),
                'sede_id' => $sedePrincipal->id,
                'telefono' => '3000000001',
                'is_active' => true,
            ]
        );
        $admin->assignRole('Super-Admin');

        $clienteEmpresa = Empresa::firstOrCreate(
            ['nit' => '900000000-2'],
            ['nombre' => 'Cliente Demo SAS', 'tipo' => 'cliente']
        );

        $sedeCliente = Sede::firstOrCreate(
            ['empresa_id' => $clienteEmpresa->id, 'nombre' => 'Sede Cliente Demo'],
            [
                'direccion' => 'Carrera Cliente 456',
                'telefono' => '3111111111',
                'email' => 'cliente@demo.test',
                'departamento_id' => $municipio->departamento_id,
                'municipio_id' => $municipio->id,
                'principal' => true,
                'activo' => true,
            ]
        );

        $clienteUser = User::firstOrCreate(
            ['email' => 'demo.cliente@cst.test'],
            [
                'name' => 'Usuario Cliente Demo',
                'password' => Hash::make('demo12345'),
                'sede_id' => $sedeCliente->id,
                'telefono' => '3111111112',
                'is_active' => true,
            ]
        );
        $clienteUser->assignRole('Cliente');

        $tipo = TipoEquipo::firstOrCreate(['tipo' => 'Ecógrafo']);
        $clasificacion = ClasificacionBiomedica::firstOrCreate(
            ['nombre' => 'Diagnóstico por imagen'],
            ['activo' => true]
        );

        $equipo = Equipo::firstOrCreate(
            ['serie' => 'DEMO-0001'],
            [
                'sede_id' => $sedeCliente->id,
                'equipo' => 'Ecógrafo Demo',
                'marca' => 'Philips',
                'modelo' => 'EPIQ 7',
                'fabricante' => 'Philips Healthcare',
                'registro_invima' => '2023MED-DEMO',
                'pais_origen' => 'Estados Unidos',
                'ubicacion' => 'Sala de ecografía',
                'inventario' => 'INV-DEMO-1',
                'code_ecri' => '1234-567',
                'tipo_equipo_id' => $tipo->id,
                'clasificacion_biomedica_id' => $clasificacion->id,
            ]
        );

        $equipo->hojaVida()->updateOrCreate(
            ['equipo_id' => $equipo->id],
            [
                'contacto_responsable' => 'Ing. Biomédico Demo',
                'telefono_responsable' => '3000000002',
                'especificaciones_tecnicas' => [
                    ['nombre' => 'Voltaje', 'valor' => '110', 'unidad' => 'V'],
                    ['nombre' => 'Frecuencia', 'valor' => '60', 'unidad' => 'Hz'],
                ],
                'fuentes_alimentacion' => ['Red eléctrica', 'UPS'],
                'sistemas_consulta' => ['PACS'],
                'uso' => 'diagnostico',
                'tipo_dispositivo' => 'activo',
                'clase_riesgo' => 'clase_iib',
            ]
        );

        $consumible = Consumible::firstOrCreate(
            ['nombre' => 'Gel conductor'],
            ['descripcion' => 'Gel para ecografía']
        );
        $accesorio = Accesorio::firstOrCreate(
            ['nombre' => 'Transductor convexo'],
            ['descripcion' => 'Transductor demo']
        );

        $equipo->consumibles()->syncWithoutDetaching([
            $consumible->id => ['cantidad' => 5, 'observaciones' => 'Stock demo'],
        ]);
        $equipo->accesorios()->syncWithoutDetaching([$accesorio->id]);

        Cronograma::firstOrCreate(
            ['equipo_id' => $equipo->id, 'year' => (string) date('Y'), 'month' => date('m')],
            [
                'clasificacion_biomedica_id' => $clasificacion->id,
                'estado' => 'pendiente',
                'periodicidad' => 'semestral',
                'fecha_programada' => now()->addDays(15)->toDateString(),
                'tecnico_id' => $admin->id,
                'observaciones' => 'Mantenimiento preventivo demo',
            ]
        );

        Ticket::firstOrCreate(
            ['empresa_id' => $clienteEmpresa->id, 'titulo' => 'Ticket de prueba demo'],
            [
                'equipo_id' => $equipo->id,
                'tecnico_id' => $admin->id,
                'descripcion' => 'Ticket generado por el seeder de demostración.',
                'estado' => 'abierto',
                'prioridad' => 'media',
                'created_by' => $admin->id,
            ]
        );

        $this->command?->info('Demo admin:   demo.admin@cst.test / demo12345');
        $this->command?->info('Demo cliente: demo.cliente@cst.test / demo12345');
    }
}
