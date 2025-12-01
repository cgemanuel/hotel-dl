{{-- resources/views/livewire/reportes/reportes-avanzados.blade.php --}}
<div class="p-6 lg:p-8 bg-white dark:bg-zinc-900">
    <div class="mb-6 border-b-4 border-indigo-700 pb-4">
        <flux:heading size="xl" class="mb-2 text-indigo-900 dark:text-indigo-100">
            Reportes Avanzados
        </flux:heading>
        <flux:subheading class="text-gray-600 dark:text-gray-400">
            Análisis detallado de rentabilidad, temporadas y comparativas
        </flux:subheading>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-blue-100 dark:bg-blue-900/20 border-l-4 border-blue-600 text-blue-800 dark:text-blue-200 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <!-- Panel de Configuración -->
    <div class="mb-6 bg-indigo-50 dark:bg-indigo-900/10 p-6 rounded-lg border-2 border-indigo-200 dark:border-indigo-800">
        <h3 class="text-lg font-semibold text-indigo-900 dark:text-indigo-100 mb-4">
            Configuración del Reporte
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Tipo de Reporte -->
            <div>
                <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-2">
                    Tipo de Reporte *
                </label>
                <select wire:model.live="tipoReporte"
                        class="w-full px-3 py-2 border border-indigo-300 dark:border-indigo-700 rounded-lg bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-indigo-500">
                    <option value="rentabilidad">Análisis de Rentabilidad</option>
                    <option value="temporadas">Análisis por Temporadas</option>
                    <option value="comparativas">Análisis Comparativo</option>
                </select>
            </div>

            <!-- Fecha Inicio -->
            <div>
                <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-2">
                    Fecha Inicio *
                </label>
                <input type="date" wire:model="fecha_inicio"
                       class="w-full px-3 py-2 border border-indigo-300 dark:border-indigo-700 rounded-lg bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-indigo-500">
                @error('fecha_inicio') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Fecha Fin -->
            <div>
                <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-2">
                    Fecha Fin *
                </label>
                <input type="date" wire:model="fecha_fin"
                       class="w-full px-3 py-2 border border-indigo-300 dark:border-indigo-700 rounded-lg bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-indigo-500">
                @error('fecha_fin') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Año de Comparación (solo para comparativas) -->
            @if($tipoReporte === 'comparativas')
            <div>
                <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-2">
                    Año a Comparar
                </label>
                <input type="number" wire:model="anio_comparacion" min="2020" max="{{ now()->year }}"
                       class="w-full px-3 py-2 border border-indigo-300 dark:border-indigo-700 rounded-lg bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-indigo-500">
            </div>
            @endif
        </div>

        <div class="flex gap-3 mt-6">
            <button wire:click="generarReporte"
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
                Generar Reporte
            </button>

            @if($reporte_generado)
            <button wire:click="limpiarReporte"
                    class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors">
                Limpiar
            </button>
            <button wire:click="exportarPDF"
                    class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                Exportar PDF
            </button>
            <button wire:click="exportarExcel"
                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                Exportar Excel
            </button>
            @endif
        </div>
    </div>

    <!-- ==================== REPORTE DE RENTABILIDAD ==================== -->
    @if($reporte_generado && $tipoReporte === 'rentabilidad' && $datosRentabilidad)
    <div class="space-y-6">
        <!-- Resumen General -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                <h3 class="text-sm font-semibold opacity-90 mb-2">Ingresos Brutos</h3>
                <p class="text-3xl font-bold">
                    ${{ number_format($datosRentabilidad['ingresos_totales']->subtotal ?? 0, 2) }}
                </p>
            </div>

            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
                <h3 class="text-sm font-semibold opacity-90 mb-2">Comisiones Totales</h3>
                <p class="text-3xl font-bold">
                    ${{ number_format($datosRentabilidad['ingresos_totales']->comisiones ?? 0, 2) }}
                </p>
            </div>

            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <h3 class="text-sm font-semibold opacity-90 mb-2">Ingresos Netos</h3>
                <p class="text-3xl font-bold">
                    ${{ number_format(($datosRentabilidad['ingresos_totales']->subtotal ?? 0) - ($datosRentabilidad['ingresos_totales']->comisiones ?? 0), 2) }}
                </p>
            </div>
        </div>

        <!-- Tasa de Ocupación -->
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-lg p-6 border-2 border-indigo-200 dark:border-indigo-800">
            <h3 class="text-xl font-bold text-indigo-900 dark:text-indigo-100 mb-4">
                Tasa de Ocupación
            </h3>
            <div class="flex items-center gap-6">
                <div class="flex-1">
                    <div class="relative pt-1">
                        <div class="flex mb-2 items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold inline-block text-indigo-600 dark:text-indigo-400">
                                    {{ number_format($datosRentabilidad['tasa_ocupacion'], 1) }}%
                                </span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-4 text-xs flex rounded-full bg-indigo-200 dark:bg-indigo-900">
                            <div style="width:{{ min($datosRentabilidad['tasa_ocupacion'], 100) }}%"
                                 class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-indigo-500 to-indigo-600"></div>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $datosRentabilidad['habitaciones_dia_ocupadas'] }} de {{ $datosRentabilidad['habitaciones_dia_disponibles'] }} habitaciones-día
                    </p>
                </div>
            </div>
        </div>

        <!-- Rentabilidad por Tipo -->
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-lg p-6 border-2 border-indigo-200 dark:border-indigo-800">
            <h3 class="text-xl font-bold text-indigo-900 dark:text-indigo-100 mb-4">
                Rentabilidad por Tipo de Habitación
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reservas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ingresos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ticket Promedio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($datosRentabilidad['rentabilidad_tipo'] as $tipo)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold capitalize">{{ $tipo->tipo }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $tipo->cantidad_reservas }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-green-600">${{ number_format($tipo->ingresos, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${{ number_format($tipo->ticket_promedio, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rentabilidad por Plataforma -->
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-lg p-6 border-2 border-indigo-200 dark:border-indigo-800">
            <h3 class="text-xl font-bold text-indigo-900 dark:text-indigo-100 mb-4">
                Rentabilidad por Plataforma
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plataforma</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">% Comisión</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reservas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ingresos Brutos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Comisión</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ingresos Netos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($datosRentabilidad['rentabilidad_plataforma'] as $plat)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ $plat->nombre_plataforma }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $plat->comision }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $plat->cantidad_reservas }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${{ number_format($plat->ingresos_brutos, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-red-600 font-bold">${{ number_format($plat->comision_total, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-green-600 font-bold">${{ number_format($plat->ingresos_brutos - $plat->comision_total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- ==================== REPORTE DE TEMPORADAS ==================== -->
    @if($reporte_generado && $tipoReporte === 'temporadas' && $datosTemporadas)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($datosTemporadas as $temporada)
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-lg p-6 border-2 border-indigo-200 dark:border-indigo-800">
            <h3 class="text-xl font-bold text-indigo-900 dark:text-indigo-100 mb-2">
                Temporada {{ $temporada['nombre'] }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $temporada['periodo'] }}</p>

            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <span class="text-sm font-medium">Total Reservas</span>
                    <span class="text-lg font-bold">{{ $temporada['stats']->total_reservas ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <span class="text-sm font-medium">Ingresos</span>
                    <span class="text-lg font-bold text-green-600">${{ number_format($temporada['stats']->ingresos ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <span class="text-sm font-medium">Precio Promedio</span>
                    <span class="text-lg font-bold">${{ number_format($temporada['stats']->precio_promedio ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <span class="text-sm font-medium">Estancia Promedio</span>
                    <span class="text-lg font-bold">{{ number_format($temporada['stats']->estancia_promedio ?? 0, 1) }} días</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- ==================== REPORTE COMPARATIVO ==================== -->
    @if($reporte_generado && $tipoReporte === 'comparativas' && $datosComparativos)
    <div class="space-y-6">
        <!-- Gráfico Comparativo -->
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-lg p-6 border-2 border-indigo-200 dark:border-indigo-800">
            <h3 class="text-xl font-bold text-indigo-900 dark:text-indigo-100 mb-4">
                Comparación {{ $datosComparativos['anio_anterior'] }} vs {{ $datosComparativos['anio_actual'] }}
            </h3>
            <canvas id="comparativoChart"></canvas>
        </div>

        <!-- Tabla Comparativa -->
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-lg p-6 border-2 border-indigo-200 dark:border-indigo-800">
            <h3 class="text-xl font-bold text-indigo-900 dark:text-indigo-100 mb-4">
                Detalle Mensual
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Mes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reservas {{ $datosComparativos['anio_anterior'] }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reservas {{ $datosComparativos['anio_actual'] }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Variación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($datosComparativos['meses_actual'] as $index => $mes)
                        @php
                            $anterior = $datosComparativos['meses_anterior'][$index]['reservas'];
                            $actual = $mes['reservas'];
                            $variacion = $anterior > 0 ? (($actual - $anterior) / $anterior) * 100 : 0;
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ $mes['mes'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $anterior }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $actual }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-bold {{ $variacion >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $variacion >= 0 ? '+' : '' }}{{ number_format($variacion, 1) }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#e5e7eb' : '#374151';

            const ctx = document.getElementById('comparativoChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode(collect($datosComparativos['meses_actual'] ?? [])->pluck('mes')) !!},
                        datasets: [
                            {
                                label: '{{ $datosComparativos["anio_anterior"] ?? "" }} - Ingresos',
                                data: {!! json_encode(collect($datosComparativos['meses_anterior'] ?? [])->pluck('ingresos')) !!},
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                tension: 0.4,
                                borderWidth: 3
                            },
                            {
                                label: '{{ $datosComparativos["anio_actual"] ?? "" }} - Ingresos',
                                data: {!! json_encode(collect($datosComparativos['meses_actual'] ?? [])->pluck('ingresos')) !!},
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                tension: 0.4,
                                borderWidth: 3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { labels: { color: textColor } }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { color: textColor }
                            },
                            x: {
                                ticks: { color: textColor }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endif

    @if(!$reporte_generado)
    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
        <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p>Configura y genera un reporte para visualizar los datos</p>
    </div>
    @endif
</div>
