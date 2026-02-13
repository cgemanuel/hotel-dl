<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ingresos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #7c3aed;
        }
        .header h1 { color: #7c3aed; font-size: 24px; margin-bottom: 10px; }
        .header p  { color: #666; font-size: 11px; margin: 3px 0; }

        .info-section {
            margin-bottom: 20px;
            background: #f9fafb;
            padding: 10px;
            border-radius: 5px;
        }
        .info-row { display: flex; justify-content: space-between; margin: 5px 0; }
        .info-label { font-weight: bold; color: #666; }

        /* ── Tarjetas ── */
        .cards-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 20px 0;
        }
        .card {
            flex: 1;
            min-width: 130px;
            padding: 14px;
            border-radius: 8px;
            border: 2px solid;
        }
        .card.efectivo       { background: #f0fdf4; border-color: #86efac; }
        .card.debito         { background: #eff6ff; border-color: #93c5fd; }
        .card.credito        { background: #eef2ff; border-color: #a5b4fc; }  /* índigo */
        .card.transferencia  { background: #faf5ff; border-color: #d8b4fe; }
        .card.total          { background: #fffbeb; border-color: #fde047; }

        .card-title  { font-size: 10px; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; color: #666; }
        .card-amount { font-size: 20px; font-weight: bold; color: #1f2937; }

        /* ── Tabla ── */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th {
            background: #7c3aed;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge.confirmadas  { background: #dcfce7; color: #166534; }
        .badge.completadas  { background: #dbeafe; color: #1e40af; }
        .badge.canceladas   { background: #fee2e2; color: #991b1b; }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .summary-box {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #7c3aed;
        }
        .summary-item { margin: 8px 0; font-size: 11px; }
        .summary-item strong { color: #1f2937; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>Hotel Don Luis - Reporte de Ingresos</h1>
        <p>
            @if($tipo_reporte === 'metodos_pago')
                Concentrado de Ingresos por Método de Pago
            @else
                Reporte de Reservas por Recepcionista
            @endif
        </p>
        <p style="margin-top: 10px;">
            <strong>Período:</strong>
            {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }}
            al {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}
        </p>
    </div>

    <!-- Info generación -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Generado por:</span>
            <span>{{ $usuario }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha de generación:</span>
            <span>{{ $fecha_generacion }}</span>
        </div>
    </div>

    @if($tipo_reporte === 'metodos_pago')

        @php
            $totalGen = $totales_metodos['total_general'];
            $pEf  = $totalGen > 0 ? ($totales_metodos['efectivo']        / $totalGen) * 100 : 0;
            $pTD  = $totalGen > 0 ? ($totales_metodos['tarjeta_debito']  / $totalGen) * 100 : 0;
            $pTC  = $totalGen > 0 ? ($totales_metodos['tarjeta_credito'] / $totalGen) * 100 : 0;
            $pTr  = $totalGen > 0 ? ($totales_metodos['transferencia']   / $totalGen) * 100 : 0;
        @endphp

        <!-- Tarjetas de totales (5 tarjetas) -->
        <div class="cards-container">

            <div class="card efectivo">
                <div class="card-title">Efectivo</div>
                <div class="card-amount">${{ number_format($totales_metodos['efectivo'], 2) }}</div>
            </div>

            <div class="card debito">
                <div class="card-title">T. Débito</div>
                <div class="card-amount">${{ number_format($totales_metodos['tarjeta_debito'], 2) }}</div>
            </div>

            <div class="card credito">
                <div class="card-title">T. Crédito</div>
                <div class="card-amount">${{ number_format($totales_metodos['tarjeta_credito'], 2) }}</div>
            </div>

            <div class="card transferencia">
                <div class="card-title">Transferencia</div>
                <div class="card-amount">${{ number_format($totales_metodos['transferencia'], 2) }}</div>
            </div>

            <div class="card total">
                <div class="card-title">Total General</div>
                <div class="card-amount">${{ number_format($totales_metodos['total_general'], 2) }}</div>
            </div>

        </div>

        <!-- Resumen -->
        <div class="summary-box">
            <div class="summary-item">
                <strong>Total de Reservas Procesadas:</strong> {{ $totales_metodos['cantidad_reservas'] }}
            </div>

            @if($totales_metodos['combinado'] > 0)
            <div class="summary-item">
                <strong>Nota:</strong> Se registraron ${{ number_format($totales_metodos['combinado'], 2) }}
                en pagos combinados (ya incluidos en los totales por método)
            </div>
            @endif

            <div class="summary-item" style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #e5e7eb;">
                <strong>Desglose de ingresos:</strong>
                <ul style="margin-top: 5px; margin-left: 20px;">
                    <li>Efectivo:           {{ number_format($pEf, 1) }}%</li>
                    <li>Tarjeta de Débito:  {{ number_format($pTD, 1) }}%</li>
                    <li>Tarjeta de Crédito: {{ number_format($pTC, 1) }}%</li>
                    <li>Transferencia:      {{ number_format($pTr, 1) }}%</li>
                </ul>
            </div>
        </div>

    @else

        <!-- Tabla de Reservas por Usuario -->
        <table>
            <thead>
                <tr>
                    <th>Recepcionista</th>
                    <th style="text-align:center;">Total Reservas</th>
                    <th style="text-align:center;">Confirmadas</th>
                    <th style="text-align:center;">Completadas</th>
                    <th style="text-align:center;">Canceladas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservas_usuario as $usuario_item)
                <tr>
                    <td><strong>{{ $usuario_item->usuario }}</strong></td>
                    <td style="text-align:center; font-weight:bold; color:#7c3aed;">
                        {{ $usuario_item->total_reservas }}
                    </td>
                    <td style="text-align:center;">
                        <span class="badge confirmadas">{{ $usuario_item->confirmadas }}</span>
                    </td>
                    <td style="text-align:center;">
                        <span class="badge completadas">{{ $usuario_item->completadas }}</span>
                    </td>
                    <td style="text-align:center;">
                        <span class="badge canceladas">{{ $usuario_item->canceladas }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding:30px; color:#999;">
                        No hay datos para mostrar
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if(count($reservas_usuario) > 0)
        <div class="summary-box">
            @php
                $total_global      = collect($reservas_usuario)->sum('total_reservas');
                $total_confirmadas = collect($reservas_usuario)->sum('confirmadas');
                $total_completadas = collect($reservas_usuario)->sum('completadas');
                $total_canceladas  = collect($reservas_usuario)->sum('canceladas');
            @endphp
            <div class="summary-item"><strong>Resumen Global:</strong></div>
            <div class="summary-item" style="margin-left:20px;">
                Total de reservas: <strong>{{ $total_global }}</strong>
                ({{ $total_confirmadas }} confirmadas,
                 {{ $total_completadas }} completadas,
                 {{ $total_canceladas }} canceladas)
            </div>
            <div class="summary-item" style="margin-left:20px;">
                Promedio por recepcionista:
                <strong>{{ number_format($total_global / count($reservas_usuario), 1) }}</strong> reservas
            </div>
        </div>
        @endif

    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Hotel Don Luis - Sistema de Gestión de Reservas</p>
        <p>C. 39 191-A, entre 38 Y 40, Centro, 97760 Valladolid, Yuc.</p>
        <p>Documento generado automáticamente el {{ $fecha_generacion }}</p>
    </div>

</body>
</html>
