<x-layouts.app :title="__('DASHBOARD')">
    @php
        // Habitaciones ocupadas vs totales
        $totalHabitaciones = \Illuminate\Support\Facades\DB::table('habitaciones')->count();
        $habitacionesOcupadas = \Illuminate\Support\Facades\DB::table('habitaciones')->where('estado', 'ocupada')->count();

        // Reservas creadas hoy
        $reservasHoy = \Illuminate\Support\Facades\DB::table('reservas')
            ->whereDate('fecha_reserva', now()->toDateString())
            ->count();

        // Reservas liberadas hoy (cambiadas a completada o cancelada hoy)
        $reservasLiberadasHoy = \Illuminate\Support\Facades\DB::table('reservas')
            ->whereIn('estado', ['completada', 'cancelada'])
            ->whereDate('updated_at', now()->toDateString())
            ->count();
    @endphp

    <!-- Contenedor principal del contenido del dashboard -->
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <!-- Sección con una cuadrícula de 3 columnas -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">

            <!-- Cuadro 1: Habitaciones Ocupadas -->
            <div class="relative aspect-video overflow-hidden rounded-xl border-2 border-green-200 dark:border-green-800 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                <div class="flex flex-col items-center justify-center h-full p-6">
                    <svg class="w-12 h-12 mb-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <h3 class="text-sm font-semibold text-green-800 dark:text-green-200 mb-2">Habitaciones Ocupadas</h3>
                    <div class="text-4xl font-bold text-green-700 dark:text-green-300">
                        {{ $habitacionesOcupadas }}<span class="text-2xl">/{{ $totalHabitaciones }}</span>
                    </div>
                    <p class="mt-2 text-xs text-green-600 dark:text-green-400">
                        {{ $totalHabitaciones > 0 ? round(($habitacionesOcupadas / $totalHabitaciones) * 100, 1) : 0 }}% de ocupación
                    </p>
                </div>
            </div>

            <!-- Cuadro 2: Reservas de Hoy -->
            <div class="relative aspect-video overflow-hidden rounded-xl border-2 border-blue-200 dark:border-blue-800 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                <div class="flex flex-col items-center justify-center h-full p-6">
                    <svg class="w-12 h-12 mb-3 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">Reservas de Hoy</h3>
                    <div class="text-5xl font-bold text-blue-700 dark:text-blue-300">
                        {{ $reservasHoy }}
                    </div>
                    <p class="mt-2 text-xs text-blue-600 dark:text-blue-400">
                        {{ now()->format('d/m/Y') }}
                    </p>
                </div>
            </div>

            <!-- Cuadro 3: Reservas Liberadas Hoy -->
            <div class="relative aspect-video overflow-hidden rounded-xl border-2 border-amber-200 dark:border-amber-800 bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/20">
                <div class="flex flex-col items-center justify-center h-full p-6">
                    <svg class="w-12 h-12 mb-3 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-200 mb-2">Liberadas Hoy</h3>
                    <div class="text-5xl font-bold text-amber-700 dark:text-amber-300">
                        {{ $reservasLiberadasHoy }}
                    </div>
                    <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">
                        Check-out programado hoy
                    </p>
                </div>
            </div>
        </div>

        <!-- Sección inferior del dashboard (es el cuadro grande de abajo)-->
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts.app>
