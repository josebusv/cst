<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Equipo;
use App\Models\HojaVida;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class HojaVidaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Ver Hoja De Vida')->only(['show']);
        $this->middleware('can:Editar Hoja De Vida')->only(['update', 'uploadImagen']);
        $this->middleware('can:Firmar Hoja De Vida')->only(['guardarFirma']);
    }

    public function show($equipoId)
    {
        $hojaVida = HojaVida::with('realizoUser', 'aproboUser')
            ->where('equipo_id', $equipoId)
            ->firstOrFail();

        return response()->json(['data' => $hojaVida]);
    }

    public function update(Request $request, $equipoId)
    {
        $hojaVida = HojaVida::firstOrCreate(
            ['equipo_id' => $equipoId],
            [
                'especificaciones_tecnicas' => [],
                'fuentes_alimentacion' => [],
                'sistemas_consulta' => [],
            ]
        );

        $validated = $request->validate([
            'contacto_responsable' => 'nullable|string|max:255',
            'telefono_responsable' => 'nullable|string|max:50',
            'especificaciones_tecnicas' => 'nullable|array',
            'fuentes_alimentacion' => 'nullable|array',
            'sistemas_consulta' => 'nullable|array',
            'accesorios' => 'nullable|array',
            'uso' => 'nullable|in:diagnostico,tratamiento,laboratorio,rehabilitacion,esterilizacion,otro',
            'tipo_dispositivo' => 'nullable|in:activo,activo_terapeutico,combinado,dm_implantable,dm_invasivo,dm_invasivo_qx',
            'clase_riesgo' => 'nullable|in:clase_i,clase_iia,clase_iib,clase_iii',
            'tipo_hoja' => 'nullable|in:electronica,endoscopia',
        ]);

        $hojaVida->update(collect($validated)->except('tipo_hoja')->toArray());

        if (!empty($validated['tipo_hoja'])) {
            $hojaVida->equipo->update(['tipo_hoja' => $validated['tipo_hoja']]);
        }

        return response()->json([
            'message' => 'Hoja de vida actualizada exitosamente',
            'data' => $hojaVida,
        ]);
    }

    public function guardarFirma(Request $request, $equipoId)
    {
        $hojaVida = HojaVida::firstOrCreate(
            ['equipo_id' => $equipoId],
            [
                'especificaciones_tecnicas' => [],
                'fuentes_alimentacion' => [],
                'sistemas_consulta' => [],
            ]
        );

        $validated = $request->validate([
            'tipo' => 'required|in:realizo,aprobo',
            'firma' => 'required|string',
            'nombre' => 'nullable|string|max:255',
            'cargo' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $data = [
            'firma_' . $validated['tipo'] => $validated['firma'],
            'nombre_' . $validated['tipo'] => $validated['nombre'] ?? null,
            'cargo_' . $validated['tipo'] => $validated['cargo'] ?? null,
        ];

        if ($validated['tipo'] === 'realizo') {
            $data['firma_realizo_user_id'] = $validated['user_id'] ?? auth()->id();
        } else {
            $data['firma_aprobo_user_id'] = $validated['user_id'] ?? auth()->id();
        }

        $hojaVida->update($data);

        return response()->json([
            'message' => 'Firma guardada exitosamente',
            'data' => $hojaVida,
        ]);
    }

    public function unidadesTecnicas()
    {
        $unidades = Cache::remember('unidades_tecnicas.agrupadas', 21600, function () {
            return \App\Models\UnidadTecnica::where('activo', true)
                ->orderBy('categoria')
                ->orderBy('nombre')
                ->get()
                ->groupBy('categoria')
                ->map(fn ($items) => $items->map(fn ($u) => [
                    'id' => $u->id,
                    'nombre' => $u->nombre,
                    'simbolo' => $u->simbolo,
                ])->values());
        });

        return response()->json(['data' => $unidades]);
    }

    public function print($equipoId, Request $request)
    {
        $token = $request->query('token');
        if (!$token) {
            abort(401, 'Token requerido');
        }

        try {
            JWTAuth::setToken($token)->authenticate();
        } catch (JWTException $e) {
            abort(401, 'Token inválido o expirado');
        }

        $equipo = \App\Models\Equipo::with([
            'sede.empresa',
            'sede.departamento',
            'sede.municipio',
            'tipoEquipo',
            'clasificacionBiomedica',
            'hojaVida.realizoUser',
            'hojaVida.aproboUser',
            'accesorios',
            'consumibles',
        ])->findOrFail($equipoId);

        $hojaVida = $equipo->hojaVida;
        $empresa = $equipo->sede?->empresa;
        $sede = $equipo->sede;
        $principal = \App\Models\Empresa::where('tipo', 'principal')->first();
        $logoPath = $empresa?->logo_url;

        $ciudad = implode(', ', array_filter([
            $sede?->municipio?->nombre,
            $sede?->departamento?->nombre,
        ]));

        $especificaciones = $hojaVida?->especificaciones_tecnicas ?? [];

        $camposElectronica = [
            ['key' => 'voltaje_max', 'label' => 'Voltaje máx.', 'categoria' => 'voltaje'],
            ['key' => 'voltaje_min', 'label' => 'Voltaje mín.', 'categoria' => 'voltaje'],
            ['key' => 'corriente_max', 'label' => 'Corriente máx.', 'categoria' => 'corriente'],
            ['key' => 'corriente_min', 'label' => 'Corriente mín.', 'categoria' => 'corriente'],
            ['key' => 'potencia', 'label' => 'Potencia', 'categoria' => 'potencia'],
            ['key' => 'frecuencia', 'label' => 'Frecuencia', 'categoria' => 'frecuencia'],
            ['key' => 'presion_max', 'label' => 'Presión máx.', 'categoria' => 'presion'],
            ['key' => 'velocidad', 'label' => 'Velocidad', 'categoria' => 'velocidad'],
            ['key' => 'capacidad', 'label' => 'Capacidad', 'categoria' => 'capacidad'],
            ['key' => 'peso', 'label' => 'Peso', 'categoria' => 'peso'],
            ['key' => 'temperatura', 'label' => 'Temperatura', 'categoria' => 'temperatura'],
            ['key' => 'dimensiones', 'label' => 'Dimensiones', 'categoria' => 'dimensiones'],
        ];

        $camposEndoscopia = [
            ['key' => 'campo_vision', 'label' => 'Campo de visión', 'categoria' => 'angulo'],
            ['key' => 'min_distancia_visible', 'label' => 'Min. Dist. Visible', 'categoria' => 'longitud'],
            ['key' => 'profundidad', 'label' => 'Profundidad', 'categoria' => 'longitud'],
            ['key' => 'velocidad_flujo_aire', 'label' => 'Veloc. flujo aire', 'categoria' => 'flujo'],
            ['key' => 'longitud_trabajo', 'label' => 'Long. de trabajo', 'categoria' => 'longitud'],
            ['key' => 'dia_tubo_insercion', 'label' => 'Dia. tubo inserción', 'categoria' => 'longitud'],
            ['key' => 'dia_canal_bx', 'label' => 'Dia. de canal bx', 'categoria' => 'longitud'],
            ['key' => 'dia_externo_distal', 'label' => 'Dia. externo distal', 'categoria' => 'longitud'],
            ['key' => 'doblado_ud', 'label' => 'Doblado U/D', 'categoria' => 'angulo'],
            ['key' => 'peso', 'label' => 'Peso', 'categoria' => 'peso'],
            ['key' => 'doblado_rl', 'label' => 'Doblado R/L', 'categoria' => 'angulo'],
            ['key' => 'longitud_total', 'label' => 'Long. Total', 'categoria' => 'longitud'],
        ];

        $campos = $equipo->tipo_hoja === 'endoscopia' ? $camposEndoscopia : $camposElectronica;

        $fuentes = [
            ['key' => 'agua', 'label' => 'Agua'],
            ['key' => 'electricidad', 'label' => 'Electricidad'],
            ['key' => 'derivados_petroleo', 'label' => 'Derv. Petróleo'],
            ['key' => 'aire', 'label' => 'Aire'],
            ['key' => 'energia_solar', 'label' => 'Energía Solar'],
            ['key' => 'vapor', 'label' => 'Vapor'],
            ['key' => 'bateria', 'label' => 'Batería'],
        ];

        $planos = [
            ['key' => 'electrico', 'label' => 'Eléctrico'],
            ['key' => 'hidraulico', 'label' => 'Hidráulico'],
            ['key' => 'neumatico', 'label' => 'Neumático'],
            ['key' => 'mecanico', 'label' => 'Mecánico'],
            ['key' => 'electronico', 'label' => 'Electrónico'],
        ];

        $tecnologias = [
            ['key' => 'electrico', 'label' => 'Eléctrico'],
            ['key' => 'electronico', 'label' => 'Electrónico'],
            ['key' => 'mecanico', 'label' => 'Mecánico'],
            ['key' => 'electromecanico', 'label' => 'Electromecánico'],
            ['key' => 'hidraulico', 'label' => 'Hidráulico'],
            ['key' => 'neumatico', 'label' => 'Neumático'],
            ['key' => 'vapor', 'label' => 'Vapor'],
            ['key' => 'energia_solar', 'label' => 'Energía solar'],
        ];

        $manuales = [
            ['key' => 'operacion', 'label' => 'Operación'],
            ['key' => 'mantenimiento', 'label' => 'Mantenimiento'],
            ['key' => 'servicio', 'label' => 'Servicio'],
            ['key' => 'ficha_tecnica', 'label' => 'Ficha técnica'],
            ['key' => 'otro', 'label' => 'Otro'],
        ];

        $html = view('hoja-vida-print', compact(
            'equipo', 'hojaVida', 'empresa', 'sede', 'principal',
            'logoPath', 'ciudad', 'especificaciones', 'campos',
            'fuentes', 'planos', 'tecnologias', 'manuales'
        ))->render();

        return response($html)->header('Content-Type', 'text/html; charset=utf-8');
    }

    public function uploadImagen(Request $request, $equipoId)
    {
        $request->validate([
            'imagen' => 'required|image|max:5120',
        ]);

        $equipo = Equipo::findOrFail($equipoId);

        if ($equipo->imagen) {
            Storage::disk('public')->delete($equipo->imagen);
        }

        $path = $request->file('imagen')->store('equipos/' . $equipoId, 'public');
        $equipo->update(['imagen' => $path]);

        return response()->json([
            'message' => 'Imagen cargada exitosamente',
            'data' => ['imagen_url' => asset('storage/' . $path)],
        ]);
    }

    public function proxyImagen(Request $request)
    {
        $url = $request->query('url');
        if (!$url) {
            abort(400, 'URL requerida');
        }

        // Si es una URL relativa tipo /storage/... o asset(), servir del disco local
        $path = null;
        if (str_starts_with($url, '/storage/') || str_contains($url, '/storage/')) {
            $segments = explode('/storage/', $url);
            $relativePath = end($segments);
            $fullPath = storage_path('app/public/' . $relativePath);
            if (file_exists($fullPath)) {
                $path = $fullPath;
            }
        }

        if (!$path && (str_starts_with($url, '/'))) {
            $fullPath = public_path($url);
            if (file_exists($fullPath)) {
                $path = $fullPath;
            }
        }

        if ($path) {
            $mime = mime_content_type($path) ?: 'image/jpeg';
            $content = file_get_contents($path);
            return response($content)
                ->header('Content-Type', $mime)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Cache-Control', 'public, max-age=86400');
        }

        // Si es URL externa, hacer proxy con curl
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            abort(400, 'URL inválida');
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $content = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($status !== 200 || !$content) {
            abort(404, 'Imagen no encontrada');
        }

        return response($content)
            ->header('Content-Type', $mime ?: 'image/jpeg')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
