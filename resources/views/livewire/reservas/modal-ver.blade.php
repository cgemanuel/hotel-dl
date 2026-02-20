<!-- Modal Ver Reserva -->
@if($mostrarModalVer && $reservaSeleccionada)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true"
     x-data="{ open: @json($mostrarModalVer) }"
     @keydown.escape="@this.call('cerrarModalVer')">

    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity"
         wire:click="cerrarModalVer"></div>

    <div class="relative inline-block align-bottom bg-white dark:bg-zinc-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full mx-4 max-h-[90vh] flex flex-col">

        <!-- Header -->
        <div class="bg-white dark:bg-zinc-900 px-4 pt-5 pb-4 sm:p-6 border-b border-zinc-200 dark:border-zinc-700 flex-shrink-0">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                        Detalles de la Reserva #{{ $reservaSeleccionada['idreservas'] ?? '' }}
                    </h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                        Folio:
                        <span class="font-semibold text-amber-600 dark:text-amber-400">
                            {{ $reservaSeleccionada['folio'] ?? '' }}
                        </span>
                    </p>
                </div>
                <button wire:click="cerrarModalVer"
                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Contenido con scroll -->
        <div class="flex-grow overflow-y-auto px-4 py-4 sm:px-6" style="max-height: calc(90vh - 180px);">
            <div class="space-y-3" x-data="{ activeSection: 'cliente' }">

                <!-- Acordeón 1: Cliente -->
                <div class="border border-blue-200 dark:border-blue-800 rounded-lg overflow-hidden">
                    <button @click="activeSection = activeSection === 'cliente' ? null : 'cliente'"
                            class="w-full px-4 py-3 bg-blue-50 dark:bg-blue-900/10 hover:bg-blue-100 dark:hover:bg-blue-900/20 transition-colors flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-100">Información del Cliente</h4>
                        </div>
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 transition-transform duration-200"
                             :class="{ 'rotate-180': activeSection === 'cliente' }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="activeSection === 'cliente'" x-collapse
                         class="px-4 py-4 space-y-4 bg-blue-50 dark:bg-blue-900/10">

                        <div>
                            <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Nombre Completo</label>
                            <p class="text-sm font-semibold dark:text-zinc-100">
                                {{ $reservaSeleccionada['nom_completo'] ?? '' }}
                            </p>
                        </div>

                        @if(!empty($reservaSeleccionada['tipo_identificacion']))
                        <div>
                            <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Tipo de Identificación</label>
                            <p class="text-sm dark:text-zinc-100">{{ $reservaSeleccionada['tipo_identificacion'] }}</p>
                        </div>
                        @endif

                        @if(!empty($reservaSeleccionada['direccion']))
                        <div>
                            <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Dirección</label>
                            <p class="text-sm dark:text-zinc-100">{{ $reservaSeleccionada['direccion'] }}</p>
                        </div>
                        @endif

                        @if(!empty($reservaSeleccionada['pais_origen']))
                        <div>
                            <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400">País</label>
                            <p class="text-sm dark:text-zinc-100">{{ $reservaSeleccionada['pais_origen'] }}</p>
                        </div>
                        @endif

                    </div>
                </div>

                <!-- Acordeón 2: Detalles de la Reserva -->
                <div class="border border-green-200 dark:border-green-800 rounded-lg overflow-hidden">
                    <button @click="activeSection = activeSection === 'reserva' ? null : 'reserva'"
                            class="w-full px-4 py-3 bg-green-50 dark:bg-green-900/10 hover:bg-green-100 dark:hover:bg-green-900/20 transition-colors flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <h4 class="text-lg font-semibold text-green-900 dark:text-green-100">Detalles de la Reserva</h4>
                        </div>
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 transition-transform duration-200"
                             :class="{ 'rotate-180': activeSection === 'reserva' }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="activeSection === 'reserva'" x-collapse
                         class="px-4 py-4 space-y-4 bg-green-50 dark:bg-green-900/10">

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-medium">Check-in</label>
                                <p class="text-sm font-semibold">
                                    {{ \Carbon\Carbon::parse($reservaSeleccionada['fecha_check_in'])->format('d/m/Y') }}
                                </p>
                            </div>
                            <div>
                                <label class="text-xs font-medium">Check-out</label>
                                <p class="text-sm font-semibold">
                                    {{ \Carbon\Carbon::parse($reservaSeleccionada['fecha_check_out'])->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-medium">Número de Personas</label>
                            <p class="text-sm">{{ $reservaSeleccionada['no_personas'] }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium">Estado</label>
                            <p class="mt-1">
                                <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full
                                    @switch($reservaSeleccionada['estado'])
                                        @case('confirmada') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @break
                                        @case('pendiente') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @break
                                        @case('cancelada') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch">
                                    {{ ucfirst($reservaSeleccionada['estado']) }}
                                </span>
                            </p>
                        </div>

                        {{-- Habitaciones --}}
                        @php
                            $habitacionesReserva = \Illuminate\Support\Facades\DB::table('habitaciones_has_reservas')
                                ->join('habitaciones', 'habitaciones.idhabitacion', '=', 'habitaciones_has_reservas.habitaciones_idhabitacion')
                                ->where('habitaciones_has_reservas.reservas_idreservas', $reservaSeleccionada['idreservas'])
                                ->select('habitaciones.no_habitacion', 'habitaciones.tipo')
                                ->get();
                        @endphp

                        @if($habitacionesReserva->count() > 0)
                        <div class="bg-white dark:bg-zinc-800 p-3 rounded-lg">
                            <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                {{ $habitacionesReserva->count() === 1 ? 'Habitación' : 'Habitaciones (' . $habitacionesReserva->count() . ')' }}
                            </label>

                            @if($habitacionesReserva->count() === 1)
                                <p class="text-lg font-bold text-green-600 dark:text-green-400 mt-1">
                                    No. {{ $habitacionesReserva->first()->no_habitacion }}
                                </p>
                                <p class="text-xs capitalize text-zinc-500 dark:text-zinc-400">
                                    {{ $habitacionesReserva->first()->tipo }}
                                </p>
                            @else
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach($habitacionesReserva as $hab)
                                        <div class="flex items-center gap-2 px-3 py-2 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg">
                                            <svg class="w-4 h-4 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                            </svg>
                                            <div>
                                                <span class="block text-sm font-bold text-green-700 dark:text-green-300 leading-tight">
                                                    No. {{ $hab->no_habitacion }}
                                                </span>
                                                <span class="block text-xs capitalize text-zinc-500 dark:text-zinc-400 leading-tight">
                                                    {{ $hab->tipo }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @endif

                        @if(!empty($reservaSeleccionada['no_espacio']))
                        <div class="bg-white dark:bg-zinc-800 p-3 rounded-lg">
                            <label class="text-xs font-medium">Estacionamiento</label>
                            <p class="text-sm font-semibold">Espacio No. {{ $reservaSeleccionada['no_espacio'] }}</p>
                            @if(!empty($reservaSeleccionada['tipo_vehiculo']))
                            <div class="mt-2 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">Tipo de Vehículo:</p>
                                <p class="text-sm font-medium">{{ $reservaSeleccionada['tipo_vehiculo'] }}</p>
                                @if(!empty($reservaSeleccionada['descripcion_vehiculo']))
                                <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">Descripción:</p>
                                <p class="text-sm">{{ $reservaSeleccionada['descripcion_vehiculo'] }}</p>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endif

                        @if(!empty($reservaSeleccionada['nombre_plataforma']))
                        <div>
                            <label class="text-xs font-medium">Plataforma</label>
                            <p class="text-sm">{{ $reservaSeleccionada['nombre_plataforma'] }}</p>
                        </div>
                        @endif

                    </div>
                </div>

                <!-- Acordeón 3: Costo Total -->
                <div class="border border-amber-200 dark:border-amber-800 rounded-lg overflow-hidden">
                    <button @click="activeSection = activeSection === 'costo' ? null : 'costo'"
                            class="w-full px-4 py-3 bg-amber-50 dark:bg-amber-900/10 hover:bg-amber-100 dark:hover:bg-amber-900/20 transition-colors flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h4 class="text-lg font-semibold text-amber-900 dark:text-amber-100">Costo Total</h4>
                        </div>
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 transition-transform duration-200"
                             :class="{ 'rotate-180': activeSection === 'costo' }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="activeSection === 'costo'" x-collapse
                         class="px-4 py-4 bg-amber-50 dark:bg-amber-900/10">

                        <div class="flex justify-between text-2xl font-bold bg-amber-100 dark:bg-amber-900/30 p-4 rounded-lg">
                            <span>Total:</span>
                            <span class="text-green-600 dark:text-green-400">
                                ${{ number_format($reservaSeleccionada['total_reserva'] ?? 0, 2) }}
                            </span>
                        </div>

                        @if(!empty($reservaSeleccionada['nombre_plataforma']) && !empty($reservaSeleccionada['comision']))
                        <div class="mt-3 pt-3 border-t border-amber-200 dark:border-amber-800">
                            <p class="text-xs text-zinc-600 dark:text-zinc-400">Plataforma: {{ $reservaSeleccionada['nombre_plataforma'] }}</p>
                            <p class="text-xs text-orange-600 dark:text-orange-400">Comisión: {{ $reservaSeleccionada['comision'] }}%</p>
                        </div>
                        @endif

                    </div>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <div class="bg-zinc-50 dark:bg-zinc-800 px-4 py-3 sm:px-6 border-t border-zinc-200 dark:border-zinc-700 flex-shrink-0">
            <button wire:click="cerrarModalVer"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:text-sm transition-colors">
                Cerrar
            </button>
        </div>

    </div>
</div>
@endif
