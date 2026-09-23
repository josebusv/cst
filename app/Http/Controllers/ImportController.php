<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Empresa;
use App\Models\Sede;
use App\Models\Equipo;
use App\Models\Departamento;
use App\Models\Municipio;

class ImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Importar');
    }

    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('archivo');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return response()->json(['message' => 'No se pudo leer el archivo'], 400);
        }

        $headers = fgetcsv($handle, 0, ';');
        if (!$headers) {
            fclose($handle);
            return response()->json(['message' => 'Archivo vacío o sin cabeceras'], 400);
        }

        $headers = array_map('trim', $headers);
        $required = ['cliente_nombre', 'sede_nombre', 'equipo'];
        $missing = array_diff($required, $headers);
        if ($missing) {
            fclose($handle);
            return response()->json([
                'message' => 'Faltan columnas requeridas: ' . implode(', ', $missing),
                'encabezados_encontrados' => $headers,
            ], 400);
        }

        $resultados = [
            'total' => 0,
            'clientes_creados' => 0,
            'clientes_existentes' => 0,
            'sedes_creadas' => 0,
            'sedes_existentes' => 0,
            'equipos_creados' => 0,
            'errores' => [],
        ];

        $clientesCache = [];
        $sedesCache = [];

        DB::beginTransaction();
        try {
        $rowNum = 1;
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rowNum++;
            $data = array_combine($headers, array_map('trim', $row));

            try {
                $clienteNombre = $data['cliente_nombre'] ?? '';
                if (empty($clienteNombre)) {
                    throw new \Exception('cliente_nombre vacío');
                }

                $sedeNombre = $data['sede_nombre'] ?? '';
                if (empty($sedeNombre)) {
                    throw new \Exception('sede_nombre vacío');
                }

                $equipoNombre = $data['equipo'] ?? '';
                if (empty($equipoNombre)) {
                    throw new \Exception('equipo vacío');
                }

                // 1. Cliente
                $clienteKey = strtolower($clienteNombre);
                $cliente = $clientesCache[$clienteKey] ?? null;
                if (!$cliente) {
                    $cliente = Empresa::where('nombre', $clienteNombre)->where('tipo', 'cliente')->first();
                }
                if (!$cliente) {
                    $cliente = Empresa::create([
                        'nombre' => $clienteNombre,
                        'nit' => $data['cliente_nit'] ?? '000000000-0',
                        'tipo' => 'cliente',
                    ]);
                    $resultados['clientes_creados']++;
                } else {
                    $resultados['clientes_existentes']++;
                }
                $clientesCache[$clienteKey] = $cliente;

                // 2. Sede
                $departamentoId = null;
                $municipioId = null;
                $deptoNombre = $data['sede_departamento'] ?? '';
                $munNombre = $data['sede_municipio'] ?? '';

                if (!empty($munNombre)) {
                    $municipio = Municipio::where('nombre', 'LIKE', $munNombre)->first();
                    if (!$municipio) {
                        // Crear municipio y departamento si no existen
                        if (!empty($deptoNombre)) {
                            $departamento = Departamento::firstOrCreate(
                                ['nombre' => $deptoNombre],
                                ['codigo' => strtoupper(substr($deptoNombre, 0, 3))]
                            );
                        } else {
                            $departamento = Departamento::first();
                        }
                        $municipio = Municipio::firstOrCreate(
                            ['nombre' => $munNombre],
                            [
                                'departamento_id' => $departamento->id,
                                'codigo' => $departamento->codigo . str_pad((string) (Municipio::max('id') + 1), 3, '0', STR_PAD_LEFT),
                            ]
                        );
                    }
                    $municipioId = $municipio->id;
                    $departamentoId = $municipio->departamento_id;
                } elseif (!empty($deptoNombre)) {
                    $departamento = Departamento::firstOrCreate(
                        ['nombre' => $deptoNombre],
                        ['codigo' => strtoupper(substr($deptoNombre, 0, 3))]
                    );
                    $departamentoId = $departamento->id;
                    $municipioId = Municipio::where('departamento_id', $departamentoId)->value('id');
                } else {
                    // Fallback: primer departamento/municipio de la BD
                    $departamentoId = Departamento::value('id');
                    $municipioId = Municipio::where('departamento_id', $departamentoId)->value('id');
                }

                $sedeKey = $cliente->id . '|' . strtolower($sedeNombre);
                $sede = $sedesCache[$sedeKey] ?? null;
                if (!$sede) {
                    $sede = Sede::where('nombre', $sedeNombre)->where('empresa_id', $cliente->id)->first();
                }
                if (!$sede) {
                    $sede = Sede::create([
                        'nombre' => $sedeNombre,
                        'empresa_id' => $cliente->id,
                        'direccion' => $data['sede_direccion'] ?? '',
                        'telefono' => $data['sede_telefono'] ?? '',
                        'email' => $data['sede_email'] ?? '',
                        'departamento_id' => $departamentoId,
                        'municipio_id' => $municipioId,
                        'activo' => true,
                    ]);
                    $resultados['sedes_creadas']++;
                } else {
                    $resultados['sedes_existentes']++;
                }
                $sedesCache[$sedeKey] = $sede;

                // 3. Equipo
                Equipo::create([
                    'sede_id' => $sede->id,
                    'equipo' => $equipoNombre,
                    'marca' => $data['marca'] ?? '',
                    'modelo' => $data['modelo'] ?? '',
                    'serie' => $data['serie'] ?? '',
                    'fabricante' => $data['fabricante'] ?? '',
                    'pais_origen' => $data['pais_origen'] ?? '',
                    'registro_invima' => $data['registro_invima'] ?? '',
                    'code_ecri' => $data['code_ecri'] ?? '',
                    'ubicacion' => $data['ubicacion'] ?? '',
                    'servicio' => $data['servicio'] ?? '',
                    'codigo' => $data['codigo'] ?? '',
                ]);
                $resultados['equipos_creados']++;
                $resultados['total']++;

            } catch (\Exception $e) {
                $resultados['errores'][] = "Fila {$rowNum}: {$e->getMessage()}";
            }
        }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        fclose($handle);

        return response()->json([
            'message' => 'Importación finalizada',
            'data' => $resultados,
        ]);
    }
}
