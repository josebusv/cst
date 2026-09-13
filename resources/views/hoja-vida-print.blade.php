<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hoja de Vida — {{ $equipo->equipo }}</title>
<style>
  @page { size: A4; margin: 12mm; }
  @media print { body { background: white !important; } .page { box-shadow: none; } }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 10px; color: #111; background: #f5f5f5; padding: 20px; }
  .page { background: white; max-width: 210mm; margin: 0 auto; padding: 20px 24px; box-shadow: 0 2px 16px rgba(0,0,0,.1); }
  .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #1a3a5c; padding-bottom: 12px; margin-bottom: 14px; }
  .header img { max-height: 48px; max-width: 200px; object-fit: contain; }
  .header h1 { font-size: 16px; color: #1a3a5c; text-transform: uppercase; letter-spacing: 0.03em; }
  .section-heading { background: #1a3a5c; color: white; font-size: 9px; font-weight: 700; text-transform: uppercase; padding: 4px 10px; text-align: center; margin-bottom: 0; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  td, th { border: 1px solid #c8d3df; padding: 3px 6px; vertical-align: middle; }
  .label { background: #f3f4f6; font-weight: 600; color: #374151; white-space: nowrap; width: 1%; }
  .value { background: #f9fbff; }
  .unit { background: #f3f4f6; text-align: center; font-size: 9px; }
  .check-cell { text-align: center; width: 18px; }
  .checked { font-weight: 700; color: #1a3a5c; }
  .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; border: 1px solid #c8d3df; margin-bottom: 12px; }
  .grid-col { border-right: 1px solid #c8d3df; }
  .grid-col:last-child { border-right: none; }
  .sub-head { background: #e5e7eb; font-weight: 700; font-size: 9px; text-align: center; padding: 4px; border-bottom: 1px solid #c8d3df; text-transform: uppercase; }
  .row-item { display: flex; align-items: center; padding: 3px 6px; border-bottom: 1px solid #c8d3df; min-height: 20px; gap: 4px; }
  .row-item:last-child { border-bottom: none; }
  .firma-grid { display: grid; grid-template-columns: 1fr 1fr; border: 1px solid #c8d3df; }
  .firma-col { padding: 12px 16px; border-right: 1px solid #c8d3df; text-align: center; }
  .firma-col:last-child { border-right: none; }
  .firma-title { font-weight: 700; font-size: 10px; text-transform: uppercase; border-bottom: 1px solid #c8d3df; padding-bottom: 6px; margin-bottom: 10px; }
  .firma-img { max-width: 100%; max-height: 60px; border: 1px solid #eee; border-radius: 4px; }
  .firma-name { font-weight: 700; font-size: 12px; margin-top: 8px; }
  .firma-role { font-size: 10px; color: #6b7280; }
  .equipo-img { max-width: 100%; max-height: 140px; object-fit: contain; border-radius: 4px; display: block; margin: 0 auto; }
</style>
</head>
<body>
<div class="page">

<div class="header">
  <div>
    @if($logoPath)
      <img src="{{ $logoPath }}" alt="Logo">
    @else
      <strong style="font-size:14px;color:#1a3a5c;">{{ $empresa->nombre ?? 'CST' }}</strong>
    @endif
  </div>
  <h1>Hoja de Vida de Equipos Médicos</h1>
</div>

<div class="section-heading">1. Datos del Propietario</div>
<table>
  <tr>
    <td class="label">Nombre</td>
    <td class="value" colspan="3">{{ $empresa->nombre ?? '' }}</td>
    <td class="label">NIT</td>
    <td class="value">{{ $empresa->nit ?? '' }}</td>
    <td class="label">Contacto</td>
    <td class="value">{{ $hojaVida->contacto_responsable ?? '' }}</td>
  </tr>
  <tr>
    <td class="label">Dirección</td>
    <td class="value" colspan="3">{{ $sede->direccion ?? '' }}</td>
    <td class="label">Teléfono</td>
    <td class="value">{{ $sede->telefono ?? '' }}</td>
    <td class="label">Correo</td>
    <td class="value">{{ $sede->email ?? '' }}</td>
  </tr>
  <tr>
    <td class="label">Ciudad</td>
    <td class="value" colspan="3">{{ $ciudad }}</td>
    <td class="label">Mantenimiento por</td>
    <td class="value">{{ $principal->nombre ?? 'CST SAS' }}</td>
    <td class="label">Teléfono</td>
    <td class="value">{{ $hojaVida->telefono_responsable ?? '' }}</td>
  </tr>
</table>

<div class="section-heading">2. Información del Equipo</div>
<table>
  <tr>
    <td class="label">Equipo</td><td class="value">{{ $equipo->equipo }}</td>
    <td class="label">Marca</td><td class="value">{{ $equipo->marca }}</td>
  </tr>
  <tr>
    <td class="label">Modelo</td><td class="value">{{ $equipo->modelo }}</td>
    <td class="label">Serie</td><td class="value">{{ $equipo->serie }}</td>
  </tr>
  <tr>
    <td class="label">Fabricante</td><td class="value">{{ $equipo->fabricante }}</td>
    <td class="label">País</td><td class="value">{{ $equipo->pais_origen }}</td>
  </tr>
  <tr>
    <td class="label">Registro Invima</td><td class="value">{{ $equipo->registro_invima }}</td>
    <td class="label">Cod. ECRI</td><td class="value">{{ $equipo->code_ecri }}</td>
  </tr>
</table>

<div class="grid-3">
  <div class="grid-col">
    <div class="sub-head">Consumibles</div>
    @foreach($equipo->consumibles ?? [] as $c)
    <div class="row-item"><span>&#9744; {{ $c->nombre }}</span></div>
    @endforeach
    @if(empty($equipo->consumibles) || count($equipo->consumibles) === 0)
    <div class="row-item" style="color:#9ca3af;">Sin consumibles</div>
    @endif
  </div>
  <div class="grid-col">
    <div class="sub-head">Accesorios</div>
    @foreach($hojaVida->accesorios ?? [] as $i => $a)
    @if(!empty($a))
    <div class="row-item"><span>{{ $i+1 }}. {{ $a }}</span></div>
    @endif
    @endforeach
    @if(empty($hojaVida->accesorios) || count(array_filter($hojaVida->accesorios ?? [])) === 0)
    <div class="row-item" style="color:#9ca3af;">Sin accesorios</div>
    @endif
  </div>
  <div style="padding:8px;text-align:center;">
    <div class="sub-head">Imagen del Equipo</div>
    @if($equipo->imagen)
    <br><img src="{{ asset('storage/' . $equipo->imagen) }}" class="equipo-img">
    @else
    <br><span style="color:#9ca3af;">Sin imagen</span>
    @endif
  </div>
</div>

<div class="section-heading">3. Información Técnica</div>
<table>
  @for($i = 0; $i < count($campos); $i += 2)
  <tr>
    <td class="label">{{ $campos[$i]['label'] }}</td>
    <td class="value">{{ $especificaciones[$campos[$i]['key']]['valor'] ?? '' }}</td>
    <td class="unit">{{ $especificaciones[$campos[$i]['key']]['unidad'] ?? '' }}</td>
    @if(isset($campos[$i+1]))
    <td class="label">{{ $campos[$i+1]['label'] }}</td>
    <td class="value">{{ $especificaciones[$campos[$i+1]['key']]['valor'] ?? '' }}</td>
    <td class="unit">{{ $especificaciones[$campos[$i+1]['key']]['unidad'] ?? '' }}</td>
    @endif
  </tr>
  @endfor
</table>

<div class="section-heading">4. Fuentes de Alimentación</div>
<table>
  <tr>
    @foreach(array_slice($fuentes, 0, 4) as $f)
    <td class="label" style="text-align:center">{{ $f['label'] }}</td>
    <td class="check-cell">
      @if($hojaVida?->fuentes_alimentacion[$f['key']] ?? false)
        <span class="checked">&#9746;</span>
      @else
        &#9744;
      @endif
    </td>
    @endforeach
    <td class="label">Otro</td>
    <td class="value">{{ $hojaVida?->fuentes_alimentacion['otro_texto'] ?? '' }}</td>
  </tr>
  <tr>
    @foreach(array_slice($fuentes, 4) as $f)
    <td class="label" style="text-align:center">{{ $f['label'] }}</td>
    <td class="check-cell">
      @if($hojaVida?->fuentes_alimentacion[$f['key']] ?? false)
        <span class="checked">&#9746;</span>
      @else
        &#9744;
      @endif
    </td>
    @endforeach
  </tr>
</table>

<div class="section-heading">5. Sistemas de Consulta</div>
<table>
  <thead><tr><th style="background:#e5e7eb;">Planos</th><th colspan="2" style="background:#e5e7eb;">Tecnología Predominante</th><th colspan="2" style="background:#e5e7eb;">Manuales</th></tr></thead>
  <tbody>
    @for($i = 0; $i < 5; $i++)
    <tr>
      @php $p = $planos[$i] ?? null; @endphp
      <td>
        @if($p)
          @if($hojaVida?->sistemas_consulta['planos'][$p['key']] ?? false)
            <span class="checked">&#9746;</span>
          @else
            &#9744;
          @endif
          {{ $p['label'] }}
        @endif
      </td>
      @php $t1 = $tecnologias[$i*2] ?? null; $t2 = $tecnologias[$i*2+1] ?? null; @endphp
      <td>
        @if($t1)
          @if($hojaVida?->sistemas_consulta['tecnologia'][$t1['key']] ?? false)
            <span class="checked">&#9746;</span>
          @else
            &#9744;
          @endif
          {{ $t1['label'] }}
        @endif
      </td>
      <td>
        @if($t2)
          @if($hojaVida?->sistemas_consulta['tecnologia'][$t2['key']] ?? false)
            <span class="checked">&#9746;</span>
          @else
            &#9744;
          @endif
          {{ $t2['label'] }}
        @endif
      </td>
      @php $m1 = $manuales[$i] ?? null; $m2 = $manuales[$i+5] ?? null; @endphp
      <td>
        @if($m1)
          @if($hojaVida?->sistemas_consulta['manuales'][$m1['key']] ?? false)
            <span class="checked">&#9746;</span>
          @else
            &#9744;
          @endif
          {{ $m1['label'] }}
        @endif
      </td>
      <td>
        @if($m2)
          @if($hojaVida?->sistemas_consulta['manuales'][$m2['key']] ?? false)
            <span class="checked">&#9746;</span>
          @else
            &#9744;
          @endif
          {{ $m2['label'] }}
        @endif
      </td>
    </tr>
    @endfor
  </tbody>
</table>

<div class="section-heading">6. Clasificación</div>
<div class="grid-3">
  <div class="grid-col">
    <div class="sub-head">Uso</div>
    @php $usos = ['diagnostico'=>'Diagnóstico','tratamiento'=>'Tratamiento','laboratorio'=>'Laboratorio','rehabilitacion'=>'Rehabilitación','esterilizacion'=>'Esterilización','otro'=>'Otro']; @endphp
    @foreach($usos as $key => $label)
    <div class="row-item">
      @if(($hojaVida->uso ?? '') === $key) <span class="checked">&#9711; &#9679;</span> @else &#9711; @endif
      {{ $label }}
    </div>
    @endforeach
  </div>
  <div class="grid-col">
    <div class="sub-head">Tipo</div>
    @php $tipos = ['activo'=>'Activo','activo_terapeutico'=>'Act. Terapéutico','combinado'=>'Combinado','dm_implantable'=>'DM Implantable','dm_invasivo'=>'DM Invasivo','dm_invasivo_qx'=>'DM Invasivo Qx']; @endphp
    @foreach($tipos as $key => $label)
    <div class="row-item">
      @if(($hojaVida->tipo_dispositivo ?? '') === $key) <span class="checked">&#9711; &#9679;</span> @else &#9711; @endif
      {{ $label }}
    </div>
    @endforeach
  </div>
  <div style="padding:4px;">
    <div class="sub-head">Riesgo</div>
    @php $riesgos = ['clase_i'=>'Clase I','clase_iia'=>'Clase IIA','clase_iib'=>'Clase IIB','clase_iii'=>'Clase III']; @endphp
    @foreach($riesgos as $key => $label)
    <div class="row-item">
      @if(($hojaVida->clase_riesgo ?? '') === $key) <span class="checked">&#9711; &#9679;</span> @else &#9711; @endif
      {{ $label }}
    </div>
    @endforeach
  </div>
</div>

<div class="firma-grid">
  <div class="firma-col">
    <div class="firma-title">Realizó</div>
    @if($hojaVida->firma_realizo ?? null)
      <img src="{{ $hojaVida->firma_realizo }}" class="firma-img">
    @else
      <div style="height:60px;display:flex;align-items:center;justify-content:center;color:#9ca3af;">___________________________</div>
    @endif
    <div class="firma-name">{{ $hojaVida->nombre_realizo ?? '' }}</div>
    <div class="firma-role">{{ $hojaVida->cargo_realizo ?? '' }}</div>
  </div>
  <div class="firma-col">
    <div class="firma-title">Aprobó</div>
    @if($hojaVida->firma_aprobo ?? null)
      <img src="{{ $hojaVida->firma_aprobo }}" class="firma-img">
    @else
      <div style="height:60px;display:flex;align-items:center;justify-content:center;color:#9ca3af;">___________________________</div>
    @endif
    <div class="firma-name">{{ $hojaVida->nombre_aprobo ?? '' }}</div>
    <div class="firma-role">{{ $hojaVida->cargo_aprobo ?? '' }}</div>
  </div>
</div>

</div>
<script>window.print();</script>
</body>
</html>
