<x-layouts.app>
<div class="p-6 lg:p-8 bg-white dark:bg-zinc-900 min-h-screen">

    {{-- ── TÍTULO ── --}}
    <div class="mb-6 border-b-4 border-amber-600 pb-4 flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="mb-1 text-amber-900 dark:text-amber-100">Dashboard</flux:heading>
            <flux:subheading class="text-gray-600 dark:text-gray-400">
                Panel de control — {{ now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}
            </flux:subheading>
        </div>
    </div>

    {{-- ── MENSAJES ── --}}
    @if(session('dashboard_message'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/20 border-l-4 border-green-600 text-green-800 dark:text-green-200 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('dashboard_message') }}
        </div>
    @endif

    @if(session('dashboard_error'))
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/20 border-l-4 border-red-600 text-red-800 dark:text-red-200 rounded-lg">
            {{ session('dashboard_error') }}
        </div>
    @endif

    @php
        $hoy        = now()->toDateString();
        $manana     = now()->addDay()->toDateString();

        // ── Reservas activas de hoy ──
        $reservasHoy = DB::table('reservas')
            ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
            ->leftJoin('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
            ->leftJoin('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
            ->whereIn('reservas.estado', ['confirmada', 'pendiente'])
            ->where('reservas.fecha_check_in', '<=', $hoy)
            ->where('reservas.fecha_check_out', '>', $hoy)
            ->select(
                'reservas.idreservas',
                'reservas.folio',
                'reservas.fecha_check_in',
                'reservas.fecha_check_out',
                'reservas.no_personas',
                'reservas.estado',
                'reservas.estacionamiento_no_espacio',
                'reservas.tipo_vehiculo',
                'reservas.descripcion_vehiculo',
                'clientes.nom_completo',

                'clientes.pais_origen',
                DB::raw('GROUP_CONCAT(DISTINCT habitaciones.no_habitacion ORDER BY habitaciones.no_habitacion SEPARATOR ", ") as no_habitacion'),
                DB::raw('GROUP_CONCAT(DISTINCT habitaciones.tipo ORDER BY habitaciones.no_habitacion SEPARATOR ", ") as tipo_habitacion')
            )
            ->groupBy(
                'reservas.idreservas', 'reservas.folio', 'reservas.fecha_check_in',
                'reservas.fecha_check_out', 'reservas.no_personas', 'reservas.estado',
                'reservas.estacionamiento_no_espacio', 'reservas.tipo_vehiculo',
                'reservas.descripcion_vehiculo', 'clientes.nom_completo',
                'clientes.pais_origen'
            )
            ->orderBy('reservas.fecha_check_in')
            ->get();

        // ── Check-ins de hoy ──
        $checkInsHoy = DB::table('reservas')
            ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
            ->leftJoin('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
            ->leftJoin('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
            ->whereIn('reservas.estado', ['confirmada', 'pendiente'])
            ->whereDate('reservas.fecha_check_in', $hoy)
            ->select(
                'reservas.idreservas', 'reservas.folio',
                'reservas.fecha_check_in', 'reservas.fecha_check_out',
                'reservas.estado', 'reservas.no_personas',
                'reservas.estacionamiento_no_espacio', 'reservas.tipo_vehiculo',
                'reservas.descripcion_vehiculo',
                'clientes.nom_completo', 'clientes.pais_origen',
                DB::raw('GROUP_CONCAT(DISTINCT habitaciones.no_habitacion ORDER BY habitaciones.no_habitacion SEPARATOR ", ") as no_habitacion'),
                DB::raw('GROUP_CONCAT(DISTINCT habitaciones.tipo ORDER BY habitaciones.no_habitacion SEPARATOR ", ") as tipo_habitacion')
            )
            ->groupBy(
                'reservas.idreservas', 'reservas.folio', 'reservas.fecha_check_in',
                'reservas.fecha_check_out', 'reservas.estado', 'reservas.no_personas',
                'reservas.estacionamiento_no_espacio', 'reservas.tipo_vehiculo',
                'reservas.descripcion_vehiculo',
                'clientes.nom_completo', 'clientes.pais_origen'
            )
            ->orderBy('reservas.fecha_check_in')
            ->get();

        // ── Check-outs de hoy ──
        $checkOutsHoy = DB::table('reservas')
            ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
            ->leftJoin('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
            ->leftJoin('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
            ->whereIn('reservas.estado', ['confirmada', 'pendiente'])
            ->whereDate('reservas.fecha_check_out', $hoy)
            ->select(
                'reservas.idreservas', 'reservas.folio',
                'reservas.fecha_check_in', 'reservas.fecha_check_out',
                'reservas.estado', 'reservas.no_personas',
                'reservas.estacionamiento_no_espacio', 'reservas.tipo_vehiculo',
                'reservas.descripcion_vehiculo',
                'clientes.nom_completo', 'clientes.pais_origen',
                DB::raw('GROUP_CONCAT(DISTINCT habitaciones.no_habitacion ORDER BY habitaciones.no_habitacion SEPARATOR ", ") as no_habitacion'),
                DB::raw('GROUP_CONCAT(DISTINCT habitaciones.tipo ORDER BY habitaciones.no_habitacion SEPARATOR ", ") as tipo_habitacion')
            )
            ->groupBy(
                'reservas.idreservas', 'reservas.folio', 'reservas.fecha_check_in',
                'reservas.fecha_check_out', 'reservas.estado', 'reservas.no_personas',
                'reservas.estacionamiento_no_espacio', 'reservas.tipo_vehiculo',
                'reservas.descripcion_vehiculo',
                'clientes.nom_completo', 'clientes.pais_origen'
            )
            ->orderBy('reservas.fecha_check_out')
            ->get();

        // ── Estadísticas rápidas ──
        $habitacionesDisponibles = DB::table('habitaciones')->where('estado', 'disponible')->count();
        $habitacionesOcupadas    = DB::table('habitaciones')->where('estado', 'ocupada')->count();
        $habitacionesTotal       = DB::table('habitaciones')->count();
        $espaciosDisponibles     = DB::table('estacionamiento')->where('estado', 'disponible')->count();
        $espaciosTotal           = DB::table('estacionamiento')->count();
    @endphp

    {{-- ── TARJETAS DE ESTADÍSTICAS ── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-700 rounded-xl p-4">
            <p class="text-xs font-semibold text-green-700 dark:text-green-300 uppercase tracking-wide">Hab. Disponibles</p>
            <p class="text-3xl font-bold text-green-700 dark:text-green-300 mt-1">{{ $habitacionesDisponibles }}</p>
            <p class="text-xs text-green-600 dark:text-green-400 mt-1">de {{ $habitacionesTotal }} total</p>
        </div>
        <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-700 rounded-xl p-4">
            <p class="text-xs font-semibold text-red-700 dark:text-red-300 uppercase tracking-wide">Hab. Ocupadas</p>
            <p class="text-3xl font-bold text-red-700 dark:text-red-300 mt-1">{{ $habitacionesOcupadas }}</p>
            <p class="text-xs text-red-600 dark:text-red-400 mt-1">Hoy activas</p>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-700 rounded-xl p-4">
            <p class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wide">Check-ins Hoy</p>
            <p class="text-3xl font-bold text-blue-700 dark:text-blue-300 mt-1">{{ $checkInsHoy->count() }}</p>
            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">Llegadas esperadas</p>
        </div>
        <div class="bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-200 dark:border-amber-700 rounded-xl p-4">
            <p class="text-xs font-semibold text-amber-700 dark:text-amber-300 uppercase tracking-wide">Estacionamiento</p>
            <p class="text-3xl font-bold text-amber-700 dark:text-amber-300 mt-1">{{ $espaciosDisponibles }}</p>
            <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">de {{ $espaciosTotal }} disponibles</p>
        </div>
    </div>

    {{-- ── MACRO: Tarjeta de reserva con detalles completos ── --}}
    @php
        /**
         * Helper para renderizar la tarjeta expandible de una reserva.
         * Se usa en las tres secciones (activas, check-ins, check-outs).
         */
        function reservaCardId($reserva) {
            return 'reserva-' . $reserva->idreservas . '-' . uniqid();
        }
    @endphp

    {{-- ══════════════════════════════════ --}}
    {{-- RESERVAS ACTIVAS HOY              --}}
    {{-- ══════════════════════════════════ --}}
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span>
            Huéspedes Activos Hoy
            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ $reservasHoy->count() }})</span>
        </h2>

        @forelse($reservasHoy as $reserva)
            @include('partials.dashboard-reserva-card', ['reserva' => $reserva, 'tipo' => 'activa'])
        @empty
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-8 text-center text-gray-500 dark:text-gray-400">
                No hay huéspedes activos hoy.
            </div>
        @endforelse
    </div>

    {{-- ══════════════════════════════════ --}}
    {{-- CHECK-INS DE HOY                  --}}
    {{-- ══════════════════════════════════ --}}
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span>
            Check-ins de Hoy
            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ $checkInsHoy->count() }})</span>
        </h2>

        @forelse($checkInsHoy as $reserva)
            @include('partials.dashboard-reserva-card', ['reserva' => $reserva, 'tipo' => 'checkin'])
        @empty
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 text-center text-gray-500 dark:text-gray-400">
                No hay check-ins programados para hoy.
            </div>
        @endforelse
    </div>

    {{-- ══════════════════════════════════ --}}
    {{-- CHECK-OUTS DE HOY                 --}}
    {{-- ══════════════════════════════════ --}}
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-orange-500 inline-block"></span>
            Check-outs de Hoy
            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ $checkOutsHoy->count() }})</span>
        </h2>

        @forelse($checkOutsHoy as $reserva)
            @include('partials.dashboard-reserva-card', ['reserva' => $reserva, 'tipo' => 'checkout'])
        @empty
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 text-center text-gray-500 dark:text-gray-400">
                No hay check-outs programados para hoy.
            </div>
        @endforelse
    </div>

</div>
</x-layouts.app>
