@php
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
@endphp

<x-layouts.app :title="__('DASHBOARD')">
    <div class="p-6 lg:p-8">
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

            // ALERTAS: Reservas próximas a check-out (HOY)
            // MODIFICADO: Se cambió telefono por correo
            $reservasCheckOutHoy = DB::table('reservas')
                ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
                ->join('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
                ->join('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
                ->select(
                    'reservas.idreservas',
                    'reservas.fecha_check_out',
                    'clientes.nom_completo',
                    'clientes.correo',
                    DB::raw('GROUP_CONCAT(habitaciones.no_habitacion SEPARATOR ", ") as habitaciones')
                )
                ->whereDate('reservas.fecha_check_out', now())
                ->where('reservas.estado', 'confirmada')
                ->groupBy(
                    'reservas.idreservas',
                    'reservas.fecha_check_out',
                    'clientes.nom_completo',
                    'clientes.correo'
                )
                ->get();

            // ALERTAS: Reservas próximas a check-out (MAÑANA)
            // MODIFICADO: Se cambió telefono por correo
            $reservasCheckOutManana = DB::table('reservas')
                ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
                ->join('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
                ->join('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
                ->select(
                    'reservas.idreservas',
                    'reservas.fecha_check_out',
                    'clientes.nom_completo',
                    'clientes.correo',
                    DB::raw('GROUP_CONCAT(habitaciones.no_habitacion SEPARATOR ", ") as habitaciones')
                )
                ->whereDate('reservas.fecha_check_out', now()->addDay())
                ->where('reservas.estado', 'confirmada')
                ->groupBy(
                    'reservas.idreservas',
                    'reservas.fecha_check_out',
                    'clientes.nom_completo',
                    'clientes.correo'
                )
                ->get();

            // Datos para gráficos
            // 1. Reservas por estado (últimos 30 días)
            $reservasPorEstado = DB::table('reservas')
                ->select('estado', DB::raw('count(*) as total'))
                ->where('fecha_reserva', '>=', now()->subDays(30))
                ->groupBy('estado')
                ->get();

            // 2. Ocupación por tipo de habitación
            $ocupacionPorTipo = DB::table('habitaciones')
                ->select(
                    'tipo',
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN estado = "ocupada" THEN 1 ELSE 0 END) as ocupadas')
                )
                ->groupBy('tipo')
                ->get();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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

            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border-l-4 border-purple-500 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Ingresos del Mes</p>
                        @php
                            $ingresosMes = DB::table('reservas')
                                ->whereMonth('fecha_reserva', now()->month)
                                ->whereYear('fecha_reserva', now()->year)
                                ->whereIn('estado', ['confirmada', 'completada'])
                                ->sum('total_reserva');
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Check-out HOY
                    </h3>
                    <span class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-full text-sm font-semibold">
                        {{ $reservasCheckOutHoy->count() }}
                    </span>
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($reservasCheckOutHoy as $reserva)
                        <div class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ $reserva->nom_completo }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Habitación(es): <span class="font-medium">{{ $reserva->habitaciones }}</span>
                                    </p>
                                </div>
                                <span class="text-xs bg-red-600 text-white px-2 py-1 rounded">HOY</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Check-out:</p>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ Carbon::parse($reserva->fecha_check_out)->format('d/m/Y') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Correo:</p>
                                    <p class="font-medium text-gray-900 dark:text-white break-all">{{ $reserva->correo }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>No hay check-outs programados para hoy</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg p-6 border-l-4 border-amber-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        Check-out MAÑANA
                    </h3>
                    <span class="px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 rounded-full text-sm font-semibold">
                        {{ $reservasCheckOutManana->count() }}
                    </span>
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($reservasCheckOutManana as $reserva)
                        <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ $reserva->nom_completo }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Habitación(es): <span class="font-medium">{{ $reserva->habitaciones }}</span>
                                    </p>
                                </div>
                                <span class="text-xs bg-amber-600 text-white px-2 py-1 rounded">MAÑANA</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Check-out:</p>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ Carbon::parse($reserva->fecha_check_out)->format('d/m/Y') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Correo:</p>
                                    <p class="font-medium text-gray-900 dark:text-white break-all">{{ $reserva->correo }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>No hay check-outs programados para mañana</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Reservas por Estado (Últimos 30 días)</h3>
                <canvas id="reservasPorEstadoChart"></canvas>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ocupación por Tipo de Habitación</h3>
                <canvas id="ocupacionPorTipoChart"></canvas>
            </div>
        </div>

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

            // 2. Gráfico de Ocupación por Tipo
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

            // ========== AUTO-REFRESH CADA 30 SEGUNDOS ==========
            setInterval(function() {
                window.location.reload();
            }, 30000); // 30000ms = 30 segundos
        </script>
    </div>
</x-layouts.app>
