<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Avanzado</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid #6366f1; }
        .header h1 { color: #6366f1; font-size: 22px; margin-bottom: 10px; }
        .header p { color: #666; font-size: 10px; margin: 3px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { background: #6366f1; color: white; padding: 10px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        .section-title { font-size: 16px; font-weight: bold; color: #6366f1; margin-top: 25px; margin-bottom: 10px; border-bottom: 2px solid #6366f1; padding-bottom: 5px; }
        .summary-box { background: #f9fafb; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #6366f1; }
        .footer { margin-top: 40px; padding-top: 15px; border-top: 2px solid #e5e7eb; text-align: center; font-size: 9px; color: #666; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 9px; font-weight: bold; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Hotel Don Luis - Reporte Avanzado</h1>
        <p>
            @if($tipo_reporte === 'rentabilidad')
                Análisis de Rentabilidad
            @elseif($tipo_reporte === 'temporadas')
                Análisis por Temporadas
            @else
                Análisis Comparativo
            @endif
        </p>
        <p><strong>Período:</strong> {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}</p>
        <p><strong>Generado:</strong> {{ $fecha_generacion }} | <strong>Usuario:</strong> {{ $usuario }}</p>
    </div>

    @if($tipo_reporte === 'rentabilidad' && $datosRentabilidad)
        <div class="section-title">Resumen Financiero</div>
        <div class="summary-box">
            <table style="border: none;">
                <tr>
                    <td style="border: none;"><strong>Ingresos Brutos:</strong></td>
                    <td style="border: none;" class="text-right">${{ number_format($datosRentabilidad['ingresos_totales']->subtotal ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td style="border: none;"><strong>Comisiones Totales:</strong></td>
                    <td style="border: none;" class="text-right">${{ number_format($datosRentabilidad['ingresos_totales']->comisiones ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td style="border: none;"><strong>Ingresos Netos:</strong></td>
                    <td style="border: none;" class="text-right font-bold">${{ number_format(($datosRentabilidad['ingresos_totales']->subtotal ?? 0) - ($datosRentabilidad['ingresos_totales']->comisiones ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td style="border: none;"><strong>Tasa de Ocupación:</strong></td>
                    <td style="border: none;" class="text-right font-bold">{{ number_format($datosRentabilidad['tasa_ocupacion'], 1) }}%</td>
                </tr>
            </table>
        </div>

        <div class="section-title">Rentabilidad por Tipo de Habitación</div>
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th class="text-right">Reservas</th>
                    <th class="text-right">Ingresos</th>
                    <th class="text-right">Ticket Promedio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datosRentabilidad['rentabilidad_tipo'] as $tipo)
                <tr>
                    <td>{{ ucfirst($tipo->tipo) }}</td>
                    <td class="text-right">{{ $tipo->cantidad_reservas }}</td>
                    <td class="text-right">${{ number_format($tipo->ingresos, 2) }}</td>
                    <td class="text-right">${{ number_format($tipo->ticket_promedio, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="section-title">Rentabilidad por Plataforma</div>
        <table>
            <thead>
                <tr>
                    <th>Plataforma</th>
                    <th class="text-right">Comisión %</th>
                    <th class="text-right">Reservas</th>
                    <th class="text-right">Ingresos Brutos</th>
                    <th class="text-right">Comisión</th>
                    <th class="text-right">Ingresos Netos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datosRentabilidad['rentabilidad_plataforma'] as $plat)
                <tr>
                    <td>{{ $plat->nombre_plataforma }}</td>
                    <td class="text-right">{{ $plat->comision }}%</td>
                    <td class="text-right">{{ $plat->cantidad_reservas }}</td>
                    <td class="text-right">${{ number_format($plat->ingresos_brutos, 2) }}</td>
                    <td class="text-right">${{ number_format($plat->comision_total, 2) }}</td>
                    <td class="text-right font-bold">${{ number_format($plat->ingresos_brutos - $plat->comision_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($tipo_reporte === 'temporadas' && $datosTemporadas)
        <div class="section-title">Análisis por Temporadas</div>
        <table>
            <thead>
                <tr>
                    <th>Temporada</th>
                    <th>Período</th>
                    <th class="text-right">Reservas</th>
                    <th class="text-right">Ingresos</th>
                    <th class="text-right">Precio Promedio</th>
                    <th class="text-right">Estancia Promedio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datosTemporadas as $temporada)
                <tr>
                    <td>{{ $temporada['nombre'] }}</td>
                    <td>{{ $temporada['periodo'] }}</td>
                    <td class="text-right">{{ $temporada['stats']->total_reservas ?? 0 }}</td>
                    <td class="text-right">${{ number_format($temporada['stats']->ingresos ?? 0, 2) }}</td>
                    <td class="text-right">${{ number_format($temporada['stats']->precio_promedio ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($temporada['stats']->estancia_promedio ?? 0, 1) }} días</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($tipo_reporte === 'comparativas' && $datosComparativos)
        <div class="section-title">Comparativo {{ $datosComparativos['anio_anterior'] }} vs {{ $datosComparativos['anio_actual'] }}</div>
        <table>
            <thead>
                <tr>
                    <th>Mes</th>
                    <th class="text-right">Reservas {{ $datosComparativos['anio_anterior'] }}</th>
                    <th class="text-right">Ingresos {{ $datosComparativos['anio_anterior'] }}</th>
                    <th class="text-right">Reservas {{ $datosComparativos['anio_actual'] }}</th>
                    <th class="text-right">Ingresos {{ $datosComparativos['anio_actual'] }}</th>
                    <th class="text-right">Variación %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datosComparativos['meses_actual'] as $index => $mes)
                    @php
                        $anterior = $datosComparativos['meses_anterior'][$index]['reservas'];
                        $actual = $mes['reservas'];
                        $variacion = $anterior > 0 ? (($actual - $anterior) / $anterior) * 100 : 0;
                    @endphp
                    <tr>
                        <td>{{ $mes['mes'] }}</td>
                        <td class="text-right">{{ $anterior }}</td>
                        <td class="text-right">${{ number_format($datosComparativos['meses_anterior'][$index]['ingresos'], 2) }}</td>
                        <td class="text-right">{{ $actual }}</td>
                        <td class="text-right">${{ number_format($mes['ingresos'], 2) }}</td>
                        <td class="text-right font-bold" style="color: {{ $variacion >= 0 ? '#16a34a' : '#dc2626' }}">
                            {{ $variacion >= 0 ? '+' : '' }}{{ number_format($variacion, 1) }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>Hotel Don Luis - Sistema de Gestión de Reservas</p>
        <p>C. 39 191-A, entre 38 Y 40, Centro, 97760 Valladolid, Yuc.</p>
        <p>Documento generado automáticamente el {{ $fecha_generacion }}</p>
    </div>
</body>
</html>