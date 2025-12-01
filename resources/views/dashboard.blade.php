@php
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
@endphp

<x-layouts.app :title="__('DASHBOARD')">
    <div class="p-6 lg:p-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400">Bienvenido al panel de control del Hotel Don Luis</p>
        </div>

        @php
            // Estadísticas generales
            $totalHabitaciones = DB::table('habitaciones')->count();
            $habitacionesOcupadas = DB::table('habitaciones')->where('estado', 'ocupada')->count();
            $reservasHoy = DB::table('reservas')->whereDate('fecha_reserva', now())->count();
            $reservasLiberadasHoy = DB::table('reservas')
                ->whereIn('estado', ['completada', 'cancelada'])
                ->whereDate('updated_at', now())
                ->count();

            // Datos para gráficos
            // 1. Reservas por estado (últimos 30 días)
            $reservasPorEstado = DB::table('reservas')
                ->select('estado', DB::raw('count(*) as total'))
                ->where('fecha_reserva', '>=', now()->subDays(30))
                ->groupBy('estado')
                ->get();

            // 2. Ingresos últimos 7 días
            $ingresosSemana = DB::table('reservas')
                ->join('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
                ->join('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
                ->select(
                    DB::raw('DATE(reservas.fecha_reserva) as fecha'),
                    DB::raw('SUM(habitaciones.precio * DATEDIFF(reservas.fecha_check_out, reservas.fecha_check_in)) as total')
                )
                ->where('reservas.fecha_reserva', '>=', now()->subDays(7))
                ->whereIn('reservas.estado', ['confirmada', 'completada'])
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get();

            // 3. Ocupación por tipo de habitación
            $ocupacionPorTipo = DB::table('habitaciones')
                ->select(
                    'tipo',
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN estado = "ocupada" THEN 1 ELSE 0 END) as ocupadas')
                )
                ->groupBy('tipo')
                ->get();

            // 4. Reservas por plataforma (último mes)
            $reservasPorPlataforma = DB::table('reservas')
                ->join('plat_reserva', 'reservas.plat_reserva_idplat_reserva', '=', 'plat_reserva.idplat_reserva')
                ->select('plat_reserva.nombre_plataforma', DB::raw('count(*) as total'))
                ->where('reservas.fecha_reserva', '>=', now()->subDays(30))
                ->groupBy('plat_reserva.idplat_reserva', 'plat_reserva.nombre_plataforma')
                ->get();
        @endphp

        <!-- Tarjetas de resumen -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Habitaciones Ocupadas -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border-l-4 border-green-500 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Habitaciones Ocupadas</p>
                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            {{ $habitacionesOcupadas }}<span class="text-xl text-gray-500">/{{ $totalHabitaciones }}</span>
                        </h3>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                            {{ $totalHabitaciones > 0 ? round(($habitacionesOcupadas / $totalHabitaciones) * 100, 1) : 0 }}% ocupación
                        </p>
                    </div>
                    <div class="bg-green-100 dark:bg-green-900/30 p-3 rounded-full">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Reservas de Hoy -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border-l-4 border-blue-500 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Reservas de Hoy</p>
                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $reservasHoy }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ now()->format('d/m/Y') }}</p>
                    </div>
                    <div class="bg-blue-100 dark:bg-blue-900/30 p-3 rounded-full">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Liberadas Hoy -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border-l-4 border-amber-500 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Liberadas Hoy</p>
                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $reservasLiberadasHoy }}</h3>
                        <p class="text-xs text-gray-500 mt-1">Check-out del día</p>
                    </div>
                    <div class="bg-amber-100 dark:bg-amber-900/30 p-3 rounded-full">
                        <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Ingresos del Mes -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border-l-4 border-purple-500 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Ingresos del Mes</p>
                        @php
                            $ingresosMes = DB::table('reservas')
                                ->join('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
                                ->join('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
                                ->whereMonth('reservas.fecha_reserva', now()->month)
                                ->whereIn('reservas.estado', ['confirmada', 'completada'])
                                ->sum(DB::raw('habitaciones.precio * GREATEST(DATEDIFF(reservas.fecha_check_out, reservas.fecha_check_in), 1)'));
                        @endphp
                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            ${{ number_format($ingresosMes, 2) }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">{{ now()->format('F Y') }}</p>
                    </div>
                    <div class="bg-purple-100 dark:bg-purple-900/30 p-3 rounded-full">
                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Gráfico: Reservas por Estado -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Reservas por Estado (Últimos 30 días)</h3>
                <canvas id="reservasPorEstadoChart"></canvas>
            </div>

            <!-- Gráfico: Ingresos Últimos 7 Días -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ingresos Últimos 7 Días</h3>
                <canvas id="ingresosSemanaChart"></canvas>
            </div>

            <!-- Gráfico: Ocupación por Tipo -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ocupación por Tipo de Habitación</h3>
                <canvas id="ocupacionPorTipoChart"></canvas>
            </div>

            <!-- Gráfico: Reservas por Plataforma -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Reservas por Plataforma (Último Mes)</h3>
                <canvas id="reservasPorPlataformaChart"></canvas>
            </div>
        </div>

        <!-- Scripts de Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            // Configuración de colores según el tema
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#e5e7eb' : '#374151';
            const gridColor = isDark ? '#374151' : '#e5e7eb';

            // 1. Gráfico de Reservas por Estado
            const reservasPorEstadoCtx = document.getElementById('reservasPorEstadoChart').getContext('2d');
            new Chart(reservasPorEstadoCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($reservasPorEstado->pluck('estado')->map(fn($e) => ucfirst($e))) !!},
                    datasets: [{
                        data: {!! json_encode($reservasPorEstado->pluck('total')) !!},
                        backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                        borderWidth: 2,
                        borderColor: isDark ? '#1f2937' : '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: textColor, padding: 15, font: { size: 12 } }
                        }
                    }
                }
            });

            // 2. Gráfico de Ingresos Últimos 7 Días
            const ingresosSemanaCtx = document.getElementById('ingresosSemanaChart').getContext('2d');
            new Chart(ingresosSemanaCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($ingresosSemana->pluck('fecha')->map(fn($f) => \Carbon\Carbon::parse($f)->format('d/m'))) !!},
                    datasets: [{
                        label: 'Ingresos ($)',
                        data: {!! json_encode($ingresosSemana->pluck('total')) !!},
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3
                    }]
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
                            ticks: { color: textColor },
                            grid: { color: gridColor }
                        },
                        x: {
                            ticks: { color: textColor },
                            grid: { color: gridColor }
                        }
                    }
                }
            });

            // 3. Gráfico de Ocupación por Tipo
            const ocupacionPorTipoCtx = document.getElementById('ocupacionPorTipoChart').getContext('2d');
            new Chart(ocupacionPorTipoCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($ocupacionPorTipo->pluck('tipo')->map(fn($t) => ucfirst($t))) !!},
                    datasets: [
                        {
                            label: 'Ocupadas',
                            data: {!! json_encode($ocupacionPorTipo->pluck('ocupadas')) !!},
                            backgroundColor: '#ef4444'
                        },
                        {
                            label: 'Disponibles',
                            data: {!! json_encode($ocupacionPorTipo->map(fn($o) => $o->total - $o->ocupadas)) !!},
                            backgroundColor: '#10b981'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            labels: { color: textColor }
                        }
                    },
                    scales: {
                        y: {
                            stacked: true,
                            ticks: { color: textColor },
                            grid: { color: gridColor }
                        },
                        x: {
                            stacked: true,
                            ticks: { color: textColor },
                            grid: { color: gridColor }
                        }
                    }
                }
            });

            // 4. Gráfico de Reservas por Plataforma
            const reservasPorPlataformaCtx = document.getElementById('reservasPorPlataformaChart').getContext('2d');
            new Chart(reservasPorPlataformaCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($reservasPorPlataforma->pluck('nombre_plataforma')) !!},
                    datasets: [{
                        label: 'Reservas',
                        data: {!! json_encode($reservasPorPlataforma->pluck('total')) !!},
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: textColor, stepSize: 1 },
                            grid: { color: gridColor }
                        },
                        x: {
                            ticks: { color: textColor },
                            grid: { display: false }
                        }
                    }
                }
            });
        </script>
    </div>
</x-layouts.app>
