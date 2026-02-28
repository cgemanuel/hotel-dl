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
            $totalHabitaciones = DB::table('habitaciones')->count();
            $habitacionesOcupadas = DB::table('habitaciones')->where('estado', 'ocupada')->count();
            $reservasHoy = DB::table('reservas')->whereDate('fecha_reserva', now())->count();
            $reservasLiberadasHoy = DB::table('reservas')
                ->whereIn('estado', ['completada', 'cancelada'])
                ->whereDate('updated_at', now())
                ->count();

            // Check-outs HOY
            $reservasCheckOutHoy = DB::table('reservas')
                ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
                ->join('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
                ->join('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
                ->select(
                    'reservas.idreservas',
                    'reservas.fecha_check_out',
                    'clientes.nom_completo',
                    DB::raw('GROUP_CONCAT(habitaciones.no_habitacion SEPARATOR ", ") as habitaciones')
                )
                ->whereDate('reservas.fecha_check_out', now())
                ->where('reservas.estado', 'confirmada')
                ->groupBy('reservas.idreservas', 'reservas.fecha_check_out', 'clientes.nom_completo')
                ->get();

            // Check-outs MAÑANA
            $reservasCheckOutManana = DB::table('reservas')
                ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
                ->join('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
                ->join('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
                ->select(
                    'reservas.idreservas',
                    'reservas.fecha_check_out',
                    'clientes.nom_completo',
                    DB::raw('GROUP_CONCAT(habitaciones.no_habitacion SEPARATOR ", ") as habitaciones')
                )
                ->whereDate('reservas.fecha_check_out', now()->addDay())
                ->where('reservas.estado', 'confirmada')
                ->groupBy('reservas.idreservas', 'reservas.fecha_check_out', 'clientes.nom_completo')
                ->get();

            // ══ NUEVO: Todas las reservas activas (confirmadas) con check-out futuro ══
            // Ordenadas por fecha de check-out más próxima
            $todasReservasActivas = DB::table('reservas')
                ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
                ->join('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
                ->join('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
                ->select(
                    'reservas.idreservas',
                    'reservas.folio',
                    'reservas.fecha_check_in',
                    'reservas.fecha_check_out',
                    'reservas.total_reserva',
                    'reservas.metodo_pago',
                    'clientes.nom_completo',
                    DB::raw('GROUP_CONCAT(habitaciones.no_habitacion ORDER BY habitaciones.no_habitacion SEPARATOR ", ") as habitaciones'),
                    DB::raw('COUNT(DISTINCT habitaciones.idhabitacion) as total_habs'),
                    DB::raw('DATEDIFF(reservas.fecha_check_out, NOW()) as dias_restantes')
                )
                ->where('reservas.estado', 'confirmada')
                ->where('reservas.fecha_check_out', '>=', now()->toDateString())
                ->groupBy(
                    'reservas.idreservas', 'reservas.folio',
                    'reservas.fecha_check_in', 'reservas.fecha_check_out',
                    'reservas.total_reserva', 'reservas.metodo_pago',
                    'clientes.nom_completo'
                )
                ->orderBy('reservas.fecha_check_out', 'asc')
                ->get();

            // Datos para gráficos
            $reservasPorEstado = DB::table('reservas')
                ->select('estado', DB::raw('count(*) as total'))
                ->where('fecha_reserva', '>=', now()->subDays(30))
                ->groupBy('estado')
                ->get();

            $ocupacionPorTipo = DB::table('habitaciones')
                ->select(
                    'tipo',
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN estado = "ocupada" THEN 1 ELSE 0 END) as ocupadas')
                )
                ->groupBy('tipo')
                ->get();
        @endphp

        {{-- ── Tarjetas de resumen ── --}}
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

        {{-- ── Check-out HOY y MAÑANA ── --}}
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
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $reserva->nom_completo }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Habitación(es): <span class="font-medium">{{ $reserva->habitaciones }}</span>
                                    </p>
                                </div>
                                <span class="text-xs bg-red-600 text-white px-2 py-1 rounded">HOY</span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Check-out: <strong>{{ Carbon::parse($reserva->fecha_check_out)->format('d/m/Y') }}</strong>
                            </p>
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
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $reserva->nom_completo }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Habitación(es): <span class="font-medium">{{ $reserva->habitaciones }}</span>
                                    </p>
                                </div>
                                <span class="text-xs bg-amber-600 text-white px-2 py-1 rounded">MAÑANA</span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Check-out: <strong>{{ Carbon::parse($reserva->fecha_check_out)->format('d/m/Y') }}</strong>
                            </p>
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

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- NUEVA SECCIÓN: Todas las habitaciones activas con su check-out   --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div class="mb-8 bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-gray-200 dark:border-zinc-700 overflow-hidden"
             x-data="{ expandido: true }">

            {{-- Header colapsable --}}
            <button @click="expandido = !expandido"
                    class="w-full flex items-center justify-between px-6 py-4 bg-gradient-to-r from-teal-700 to-teal-600 hover:from-teal-800 hover:to-teal-700 transition-all text-left">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <div>
                        <h3 class="text-lg font-bold text-white">Todas las Habitaciones Activas</h3>
                        <p class="text-teal-200 text-sm">Check-outs próximos ordenados por fecha — haz check-out desde Gestión de Reservas</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-white/20 text-white rounded-full text-sm font-semibold">
                        {{ $todasReservasActivas->count() }} activa{{ $todasReservasActivas->count() != 1 ? 's' : '' }}
                    </span>
                    <svg class="w-5 h-5 text-white transition-transform duration-200"
                         :class="expandido ? 'rotate-180' : 'rotate-0'"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>

            <div x-show="expandido" x-collapse>
                @if($todasReservasActivas->count() > 0)
                    {{-- Vista tabla para pantallas medianas+ --}}
                    <div class="overflow-x-auto hidden md:block">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                            <thead class="bg-teal-50 dark:bg-teal-900/20">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-teal-800 dark:text-teal-300 uppercase tracking-wider">Huésped</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-teal-800 dark:text-teal-300 uppercase tracking-wider">Hab.</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-teal-800 dark:text-teal-300 uppercase tracking-wider">Check-in</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-teal-800 dark:text-teal-300 uppercase tracking-wider">Check-out</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-teal-800 dark:text-teal-300 uppercase tracking-wider">Noches</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-teal-800 dark:text-teal-300 uppercase tracking-wider">Días restantes</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-teal-800 dark:text-teal-300 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-teal-800 dark:text-teal-300 uppercase tracking-wider">Folio</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-100 dark:divide-zinc-800">
                                @foreach($todasReservasActivas as $reserva)
                                    @php
                                        $noches = Carbon::parse($reserva->fecha_check_in)->diffInDays(Carbon::parse($reserva->fecha_check_out));
                                        $diasRestantes = (int) $reserva->dias_restantes;
                                        // Color según urgencia
                                        if ($diasRestantes === 0) {
                                            $rowClass = 'bg-red-50 dark:bg-red-900/10';
                                            $badgeClass = 'bg-red-600 text-white';
                                            $badgeText = 'HOY';
                                        } elseif ($diasRestantes === 1) {
                                            $rowClass = 'bg-amber-50 dark:bg-amber-900/10';
                                            $badgeClass = 'bg-amber-500 text-white';
                                            $badgeText = 'MAÑANA';
                                        } elseif ($diasRestantes <= 3) {
                                            $rowClass = 'bg-yellow-50 dark:bg-yellow-900/10';
                                            $badgeClass = 'bg-yellow-500 text-white';
                                            $badgeText = $diasRestantes . ' días';
                                        } else {
                                            $rowClass = '';
                                            $badgeClass = 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300';
                                            $badgeText = $diasRestantes . ' días';
                                        }
                                    @endphp
                                    <tr class="hover:bg-teal-50/50 dark:hover:bg-teal-900/10 transition-colors {{ $rowClass }}">
                                        <td class="px-4 py-3">
                                            <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $reserva->nom_completo }}</p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300">
                                                {{ $reserva->habitaciones }}
                                                @if($reserva->total_habs > 1)
                                                    <span class="ml-1 text-teal-500">({{ $reserva->total_habs }})</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                            {{ Carbon::parse($reserva->fecha_check_in)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ Carbon::parse($reserva->fecha_check_out)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ $noches }} noche{{ $noches != 1 ? 's' : '' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                                {{ $badgeText }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-bold text-amber-600 dark:text-amber-400">
                                            ${{ number_format($reserva->total_reserva, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 font-mono">
                                            {{ $reserva->folio }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Vista tarjetas para móvil --}}
                    <div class="md:hidden divide-y divide-gray-100 dark:divide-zinc-800">
                        @foreach($todasReservasActivas as $reserva)
                            @php
                                $noches = Carbon::parse($reserva->fecha_check_in)->diffInDays(Carbon::parse($reserva->fecha_check_out));
                                $diasRestantes = (int) $reserva->dias_restantes;
                                if ($diasRestantes === 0) {
                                    $cardBorder = 'border-l-4 border-red-500';
                                    $badgeClass = 'bg-red-600 text-white';
                                    $badgeText = 'HOY';
                                } elseif ($diasRestantes === 1) {
                                    $cardBorder = 'border-l-4 border-amber-500';
                                    $badgeClass = 'bg-amber-500 text-white';
                                    $badgeText = 'MAÑANA';
                                } elseif ($diasRestantes <= 3) {
                                    $cardBorder = 'border-l-4 border-yellow-400';
                                    $badgeClass = 'bg-yellow-500 text-white';
                                    $badgeText = $diasRestantes . ' días';
                                } else {
                                    $cardBorder = 'border-l-4 border-teal-400';
                                    $badgeClass = 'bg-teal-100 text-teal-800';
                                    $badgeText = $diasRestantes . ' días';
                                }
                            @endphp
                            <div class="p-4 {{ $cardBorder }}">
                                <div class="flex justify-between items-start mb-2">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $reserva->nom_completo }}</p>
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">{{ $badgeText }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <div>Hab: <span class="font-medium text-gray-900 dark:text-white">{{ $reserva->habitaciones }}</span></div>
                                    <div>{{ $noches }} noche{{ $noches != 1 ? 's' : '' }}</div>
                                    <div>Check-in: <span class="font-medium">{{ Carbon::parse($reserva->fecha_check_in)->format('d/m/Y') }}</span></div>
                                    <div>Check-out: <span class="font-medium">{{ Carbon::parse($reserva->fecha_check_out)->format('d/m/Y') }}</span></div>
                                    <div class="col-span-2 flex justify-between items-center pt-1 border-t border-gray-100 dark:border-zinc-700 mt-1">
                                        <span class="text-xs text-gray-400 font-mono">{{ $reserva->folio }}</span>
                                        <span class="font-bold text-amber-600 dark:text-amber-400">${{ number_format($reserva->total_reserva, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Enlace a reservas --}}
                    <div class="px-6 py-3 bg-gray-50 dark:bg-zinc-800/50 border-t border-gray-200 dark:border-zinc-700 flex justify-end">
                        <a href="{{ route('reservas.index') }}"
                           class="inline-flex items-center gap-2 text-sm font-medium text-teal-700 dark:text-teal-400 hover:text-teal-900 dark:hover:text-teal-200 transition-colors">
                            Gestionar reservas
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                @else
                    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-lg font-medium">No hay reservas activas en este momento</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Gráficos ── --}}
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
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#e5e7eb' : '#374151';
            const gridColor = isDark ? '#374151' : '#e5e7eb';

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
                    plugins: { legend: { labels: { color: textColor } } },
                    scales: {
                        y: { stacked: true, ticks: { color: textColor }, grid: { color: gridColor } },
                        x: { stacked: true, ticks: { color: textColor }, grid: { color: gridColor } }
                    }
                }
            });

            setInterval(function() { window.location.reload(); }, 30000);
        </script>
    </div>
</x-layouts.app>
