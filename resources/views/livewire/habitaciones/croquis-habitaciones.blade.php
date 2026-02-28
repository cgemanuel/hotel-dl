{{-- Contenedor principal del croquis de habitaciones --}}
<div class="w-full p-4 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/20 border-l-4 border-green-600 text-green-800 dark:text-green-200 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/20 border-l-4 border-red-600 text-red-800 dark:text-red-200 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="px-2 md:px-4">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Croquis de Habitaciones</h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Haz clic en una habitación para ver detalles</p>

        {{-- SELECTOR DE FECHA --}}
        <div class="flex flex-wrap items-center gap-3 mb-5 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-sm font-semibold text-blue-800 dark:text-blue-200">
                    Ver disponibilidad para:
                </span>
            </div>

            <input
                type="date"
                wire:model.live="fechaConsulta"
                class="px-3 py-2 border-2 border-blue-300 dark:border-blue-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
            />

            <button
                wire:click="irAHoy"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-colors"
            >
                Ir a Hoy
            </button>
        </div>

        {{-- Botones de plantas --}}
        <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
            @foreach($plantas as $index => $planta)
                @php
                    $nombresMostrar = ['Planta 1', 'Planta 2', 'Planta 3'];
                @endphp
                <button
                    wire:click="cambiarPlanta('{{ $planta }}')"
                    class="px-6 py-2 rounded-lg font-semibold transition-all duration-200 whitespace-nowrap
                    {{ $plantaActiva === $planta
                        ? 'bg-amber-600 text-white'
                        : 'bg-green-700 text-gray-200 hover:bg-green-600' }}"
                >
                    {{ $nombresMostrar[$index] ?? $planta }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Panel de Resumen por Planta --}}
    <div class="bg-gray-800 bg-opacity-30 rounded-xl p-6 mb-8 mx-2 md:mx-4">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">Estado de Habitaciones</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            Para el día
            <span class="font-semibold text-blue-600 dark:text-blue-400">
                {{ \Carbon\Carbon::parse($fechaConsulta ?: now())->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
            </span>
        </p>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-gray-700 bg-opacity-50 rounded-lg p-4">
                <p class="text-gray-300 text-sm">Total de Habitaciones</p>
                <p class="text-3xl font-bold text-white mt-2">{{ $totalHabitaciones }}</p>
            </div>
            <div class="bg-green-500 bg-opacity-20 rounded-lg p-4">
                <p class="text-green-100 text-sm">Disponibles</p>
                <p class="text-3xl font-bold text-green-300 mt-2">{{ $disponibles }}</p>
            </div>
            <div class="bg-red-500 bg-opacity-20 rounded-lg p-4">
                <p class="text-red-100 text-sm">Ocupadas</p>
                <p class="text-3xl font-bold text-red-300 mt-2">{{ $ocupadas }}</p>
            </div>
            <div class="bg-yellow-500 bg-opacity-20 rounded-lg p-4">
                <p class="text-yellow-100 text-sm">En Mantenimiento</p>
                <p class="text-3xl font-bold text-yellow-300 mt-2">{{ $mantenimiento }}</p>
            </div>
        </div>
    </div>

    {{-- Cuadrícula de habitaciones --}}
    <div class="min-h-[400px] mb-8">
        @if(count($habitacionesActuales) > 0)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 px-4">
                @foreach($habitacionesActuales as $habitacion)
                    @php
                        $estadoNormalizado = strtolower(str_replace(' ', '_', trim($habitacion->estado ?? '')));

                        if ($estadoNormalizado === 'disponible') {
                            $bgClase    = 'bg-green-800 hover:bg-green-900';
                            $borderClase = 'border-green-400';
                            $textClase  = 'text-white';
                            $tagClase   = 'bg-green-100 text-green-800';
                        } elseif ($estadoNormalizado === 'ocupada') {
                            $bgClase    = 'bg-red-800 hover:bg-red-700';
                            $borderClase = 'border-red-400';
                            $textClase  = 'text-white';
                            $tagClase   = 'bg-red-100 text-red-800';
                        } elseif (in_array($estadoNormalizado, ['en_mantenimiento', 'mantenimiento'])) {
                            $bgClase    = 'bg-yellow-500 hover:bg-yellow-600';
                            $borderClase = 'border-yellow-400';
                            $textClase  = 'text-white';
                            $tagClase   = 'bg-yellow-100 text-yellow-800';
                        } else {
                            $bgClase    = 'bg-gray-600 hover:bg-gray-700';
                            $borderClase = 'border-gray-400';
                            $textClase  = 'text-white';
                            $tagClase   = 'bg-gray-100 text-gray-800';
                        }
                    @endphp

                    <button
                        wire:click="seleccionarHabitacion({{ $habitacion->idhabitacion }})"
                        class="w-full h-72 rounded-2xl overflow-hidden shadow-xl border-4 {{ $bgClase }} {{ $borderClase }} {{ $textClase }} transition-all duration-500 transform hover:scale-105 hover:shadow-2xl"
                    >
                        <div class="h-full flex flex-col items-center justify-center p-5">
                            <span class="text-5xl font-bold mb-3">{{ $habitacion->no_habitacion }}</span>
                            <h3 class="text-xl font-semibold capitalize mb-2">{{ $habitacion->tipo }}</h3>
                            <span class="px-3 py-1.5 rounded-full text-sm font-medium capitalize {{ $tagClase }}">
                                {{ str_replace('_', ' ', $habitacion->estado) }}
                            </span>

                            {{-- Mostrar check-out y noches si la habitación está ocupada --}}
                            @if($estadoNormalizado === 'ocupada' && $habitacion->reserva_id)
                                @php
                                    $resInfo = \Illuminate\Support\Facades\DB::table('reservas')
                                        ->where('idreservas', $habitacion->reserva_id)
                                        ->select('fecha_check_out', 'fecha_check_in')
                                        ->first();
                                @endphp
                                @if($resInfo)
                                    @php
                                        $noches = \Carbon\Carbon::parse($resInfo->fecha_check_in)
                                            ->diffInDays(\Carbon\Carbon::parse($resInfo->fecha_check_out));
                                    @endphp
                                    <div class="mt-3 w-full space-y-1">
                                        <div class="flex items-center justify-center gap-1.5 text-xs font-medium opacity-90">
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span>Sal: {{ \Carbon\Carbon::parse($resInfo->fecha_check_out)->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="flex items-center justify-center gap-1.5 text-xs font-medium opacity-80">
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                            </svg>
                                            <span>{{ $noches }} noche{{ $noches != 1 ? 's' : '' }}</span>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        @else
            <div class="text-center text-gray-600 dark:text-gray-300 py-12">
                <p class="text-xl">No hay habitaciones en esta planta</p>
            </div>
        @endif
    </div>

    {{-- Modal de información de habitación --}}
    @if($mostrarModal && $habitacionSeleccionada)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
             wire:click.self="cerrarModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">

                {{-- Header --}}
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800 z-10">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            Habitación {{ $habitacionSeleccionada['no_habitacion'] }}
                        </h3>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">
                            Consultando: {{ \Carbon\Carbon::parse($fechaConsulta ?: now())->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                        </p>
                    </div>
                    <button wire:click="cerrarModal" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-4">

                    {{-- Información general --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tipo</p>
                            <p class="font-semibold text-gray-900 dark:text-white mt-1 capitalize">
                                {{ $habitacionSeleccionada['tipo'] }}
                            </p>
                        </div>
                    </div>

                    {{-- Estado actual --}}
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Estado para la fecha consultada</p>
                        <div class="flex items-center gap-2 mb-3">
                            @php
                                $modalEstado = strtolower(str_replace(' ', '_', trim($habitacionSeleccionada['estado'])));
                                $colorBg = match($modalEstado) {
                                    'disponible' => 'bg-green-500',
                                    'ocupada' => 'bg-red-500',
                                    'en_mantenimiento', 'mantenimiento' => 'bg-yellow-500',
                                    default => 'bg-gray-500',
                                };
                            @endphp
                            <div class="w-4 h-4 rounded {{ $colorBg }}"></div>
                            <p class="font-semibold text-gray-900 dark:text-white capitalize">
                                {{ str_replace('_', ' ', $habitacionSeleccionada['estado']) }}
                            </p>
                        </div>

                        {{-- Botones para cambiar estado físico (mantenimiento) --}}
                        <div class="flex flex-col gap-2 mt-4">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Cambiar estado físico:
                                <span class="text-xs font-normal text-gray-500 dark:text-gray-400">
                                    (disponible/ocupada se calculan por reservas automáticamente)
                                </span>
                            </p>
                            <button
                                wire:click="cambiarEstadoHabitacion({{ $habitacionSeleccionada['idhabitacion'] }}, 'disponible')"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors text-sm"
                                wire:confirm="¿Desea marcar como Disponible? Esto quitará el mantenimiento si lo tenía."
                            >
                                Quitar Mantenimiento / Marcar Disponible
                            </button>
                            <button
                                wire:click="cambiarEstadoHabitacion({{ $habitacionSeleccionada['idhabitacion'] }}, 'en_mantenimiento')"
                                class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg font-medium transition-colors text-sm"
                                wire:confirm="¿Desea poner en Mantenimiento?"
                            >
                                Marcar en Mantenimiento
                            </button>
                        </div>
                    </div>

                    {{-- Reserva para esa fecha (si hay) --}}
                    @if($habitacionSeleccionada['estado'] === 'ocupada' && isset($habitacionSeleccionada['nom_completo']))
                        <hr class="my-4 dark:border-gray-700">

                        <div class="rounded-xl overflow-hidden border border-blue-200 dark:border-blue-700 shadow-sm"
                             x-data="{ openReserva: true }">

                            <button
                                @click="openReserva = !openReserva"
                                class="w-full flex items-center justify-between px-5 py-4 bg-blue-600 hover:bg-blue-700 transition-colors text-left focus:outline-none"
                            >
                                <span class="flex items-center gap-2 text-white font-bold text-base">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    Reserva para este día
                                </span>
                                <svg class="w-5 h-5 text-white transform transition-transform duration-200"
                                     :class="openReserva ? 'rotate-180' : 'rotate-0'"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="openReserva"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 -translate-y-1"
                                 class="bg-blue-50 dark:bg-blue-900/10 divide-y divide-blue-100 dark:divide-blue-800"
                            >

                                {{-- SUB-SECCIÓN 1: Datos del huésped --}}
                                <div class="p-5" x-data="{ openDatos: true }">
                                    <button @click="openDatos = !openDatos"
                                            class="w-full flex items-center justify-between mb-3 text-left focus:outline-none">
                                        <h5 class="text-sm font-bold text-blue-800 dark:text-blue-300 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Información del Huésped
                                        </h5>
                                        <svg class="w-4 h-4 text-blue-500 transform transition-transform duration-150"
                                             :class="openDatos ? 'rotate-180' : 'rotate-0'"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div x-show="openDatos" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                            {{-- Folio --}}
                                            @if(isset($habitacionSeleccionada['folio']))
                                                <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 border border-blue-100 dark:border-blue-800">
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Folio</p>
                                                    <p class="font-bold text-gray-900 dark:text-white text-lg">{{ $habitacionSeleccionada['folio'] }}</p>
                                                </div>
                                            @endif

                                            {{-- Cliente --}}
                                            <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 border border-blue-100 dark:border-blue-800">
                                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Cliente</p>
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ $habitacionSeleccionada['nom_completo'] }}</p>
                                            </div>

                                            {{-- Check-in --}}
                                            <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 border border-blue-100 dark:border-blue-800">
                                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Check-in</p>
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ \Carbon\Carbon::parse($habitacionSeleccionada['fecha_check_in'])->format('d/m/Y') }}
                                                </p>
                                            </div>

                                            {{-- ✅ Check-out con noches --}}
                                            <div class="bg-white dark:bg-zinc-800 rounded-lg p-3 border border-blue-100 dark:border-blue-800">
                                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Check-out</p>
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ \Carbon\Carbon::parse($habitacionSeleccionada['fecha_check_out'])->format('d/m/Y') }}
                                                </p>
                                                @php
                                                    $nochesModal = \Carbon\Carbon::parse($habitacionSeleccionada['fecha_check_in'])
                                                        ->diffInDays(\Carbon\Carbon::parse($habitacionSeleccionada['fecha_check_out']));
                                                @endphp
                                                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1 font-semibold">
                                                    {{ $nochesModal }} noche{{ $nochesModal != 1 ? 's' : '' }}
                                                </p>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                {{-- SUB-SECCIÓN 2: Servicios --}}
                                <div class="p-5" x-data="{ openServicios: true }">
                                    <button @click="openServicios = !openServicios"
                                            class="w-full flex items-center justify-between mb-3 text-left focus:outline-none">
                                        <h5 class="text-sm font-bold text-blue-800 dark:text-blue-300 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                            Servicios Adicionales
                                            @php $totalServicios = count($habitacionSeleccionada['servicios'] ?? []); @endphp
                                            <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-semibold
                                                {{ $totalServicios > 0 ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                                                {{ $totalServicios }}
                                            </span>
                                        </h5>
                                        <svg class="w-4 h-4 text-blue-500 transform transition-transform duration-150"
                                             :class="openServicios ? 'rotate-180' : 'rotate-0'"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div x-show="openServicios" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                                        @if(isset($habitacionSeleccionada['servicios']) && count($habitacionSeleccionada['servicios']) > 0)
                                            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-blue-100 dark:border-blue-800 overflow-hidden">
                                                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                                                    @foreach($habitacionSeleccionada['servicios'] as $servicio)
                                                        <li class="px-4 py-3 flex justify-between items-start text-sm hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                                                            <div class="flex items-start gap-2">
                                                                <svg class="w-4 h-4 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                                <span class="text-gray-700 dark:text-gray-300">
                                                                    {{ $servicio['concepto'] ?? $servicio['descripcion'] ?? 'Servicio sin descripción' }}
                                                                </span>
                                                            </div>
                                                            @if(!empty($servicio['precio']))
                                                                <span class="font-semibold text-green-600 dark:text-green-400 ml-3 flex-shrink-0">
                                                                    ${{ number_format($servicio['precio'], 2) }}
                                                                </span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                @php $totalPrecio = collect($habitacionSeleccionada['servicios'])->sum('precio'); @endphp
                                                @if($totalPrecio > 0)
                                                    <div class="bg-gray-50 dark:bg-zinc-700/50 px-4 py-2 flex justify-end items-center gap-2">
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">Total Servicios:</span>
                                                        <span class="font-bold text-gray-900 dark:text-white">
                                                            ${{ number_format($totalPrecio, 2) }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="flex items-center gap-2 px-3 py-4 bg-white dark:bg-zinc-800 rounded-lg border border-blue-100 dark:border-blue-800 text-gray-500 dark:text-gray-400">
                                                <span class="text-sm italic">No hay servicios adicionales registrados.</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- SUB-SECCIÓN 3: Vehículo --}}
                                <div class="p-5" x-data="{ openVehiculo: true }">
                                    <button @click="openVehiculo = !openVehiculo"
                                            class="w-full flex items-center justify-between mb-3 text-left focus:outline-none">
                                        <h5 class="text-sm font-bold text-blue-800 dark:text-blue-300 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2m0 0h10m-10 0H3m10 0h3m0 0l3-3m-3 3V6a1 1 0 011-1h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V16" />
                                            </svg>
                                            Datos del Vehículo
                                            @if(!empty($habitacionSeleccionada['vehiculo_tipo']))
                                                <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-500 text-white">Registrado</span>
                                            @else
                                                <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Sin registro</span>
                                            @endif
                                        </h5>
                                        <svg class="w-4 h-4 text-blue-500 transform transition-transform duration-150"
                                             :class="openVehiculo ? 'rotate-180' : 'rotate-0'"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div x-show="openVehiculo" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                                        @if(!empty($habitacionSeleccionada['vehiculo_tipo']) || !empty($habitacionSeleccionada['vehiculo_descripcion']))
                                            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-blue-100 dark:border-blue-800 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700">
                                                @if(!empty($habitacionSeleccionada['vehiculo_tipo']))
                                                    <div class="flex items-center justify-between px-4 py-3">
                                                        <span class="text-xs text-gray-500 uppercase font-medium">Tipo</span>
                                                        <span class="text-sm font-semibold text-gray-900 dark:text-white capitalize">{{ $habitacionSeleccionada['vehiculo_tipo'] }}</span>
                                                    </div>
                                                @endif
                                                @if(!empty($habitacionSeleccionada['vehiculo_descripcion']))
                                                    <div class="flex items-start justify-between px-4 py-3">
                                                        <span class="text-xs text-gray-500 uppercase font-medium">Descripción</span>
                                                        <span class="text-sm text-gray-900 dark:text-white text-right max-w-[55%]">{{ $habitacionSeleccionada['vehiculo_descripcion'] }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="flex items-center gap-2 px-3 py-4 bg-white dark:bg-zinc-800 rounded-lg border border-blue-100 dark:border-blue-800 text-gray-500 dark:text-gray-400">
                                                <span class="text-sm italic">No se registró información de vehículo.</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>

                    @elseif($habitacionSeleccionada['estado'] === 'disponible')
                        <p class="text-center py-4 text-green-600 dark:text-green-400 font-semibold">
                            Habitación disponible para esta fecha
                        </p>
                    @elseif(in_array($habitacionSeleccionada['estado'], ['en_mantenimiento', 'mantenimiento']))
                        <p class="text-center py-4 text-yellow-600 dark:text-yellow-400 font-semibold">
                            En mantenimiento
                        </p>
                    @endif

                    <hr class="dark:border-gray-700">

                    {{-- Botón historial --}}
                    <button
                        wire:click="toggleHistorial"
                        class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $mostrarHistorial ? 'Ocultar Historial' : 'Ver Historial de Reservas' }}
                    </button>

                    @if($mostrarHistorial)
                        <div class="mt-4 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <div class="bg-indigo-600 text-white px-4 py-2 font-semibold">
                                Historial de Reservas
                            </div>
                            @if(count($historialReservas) > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Folio</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Check-in</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Check-out</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Noches</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($historialReservas as $reserva)
                                            @php
                                                $nochesHist = \Carbon\Carbon::parse($reserva->fecha_check_in)
                                                    ->diffInDays(\Carbon\Carbon::parse($reserva->fecha_check_out));
                                            @endphp
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-white font-mono">{{ $reserva->folio ?? 'N/A' }}</td>
                                                <td class="px-3 py-2 text-sm">
                                                    <div class="text-gray-900 dark:text-white font-medium">{{ $reserva->nom_completo }}</div>
                                                </td>
                                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-white">
                                                    {{ \Carbon\Carbon::parse($reserva->fecha_check_in)->format('d/m/Y') }}
                                                </td>
                                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-white">
                                                    {{ \Carbon\Carbon::parse($reserva->fecha_check_out)->format('d/m/Y') }}
                                                </td>
                                                {{-- ✅ Columna noches en historial --}}
                                                <td class="px-3 py-2 text-sm text-center text-gray-600 dark:text-gray-400 font-medium">
                                                    {{ $nochesHist }}n
                                                </td>
                                                <td class="px-3 py-2">
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                                        {{ $reserva->estado === 'confirmada' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : '' }}
                                                        {{ $reserva->estado === 'completada' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' : '' }}
                                                        {{ $reserva->estado === 'cancelada'  ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' : '' }}
                                                        {{ $reserva->estado === 'pendiente'  ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300' : '' }}">
                                                        {{ ucfirst($reserva->estado) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No hay historial de reservas para esta habitación
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 rounded-b-lg sticky bottom-0">
                    <button wire:click="cerrarModal"
                            class="w-full px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
