<!-- Modal Ver Reserva -->
@if($mostrarModalVer && $reservaSeleccionada)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true"
     x-data="{ open: @json($mostrarModalVer) }"
     @keydown.escape="@this.call('cerrarModalVer')">

    <!-- Overlay -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity"
         wire:click="cerrarModalVer"></div>

    <!-- Modal Panel (mismo estilo que Editar) -->
    <div class="relative inline-block align-bottom bg-white dark:bg-zinc-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full mx-4">

        <div class="bg-white dark:bg-zinc-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6 border-b border-zinc-200 dark:border-zinc-700 pb-4">
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Content en 3 Columnas -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Columna 1 -->
                <div
                    class="space-y-4 bg-blue-50 dark:bg-blue-900/10 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                    <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-100 flex items-center gap-2">
                        Información del Cliente
                    </h4>

                    <div>
                        <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Nombre Completo</label>
                        <p class="text-sm font-semibold dark:text-zinc-100">
                            {{ $reservaSeleccionada['nom_completo'] ?? '' }}
                        </p>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Correo</label>
                        <p class="text-sm dark:text-zinc-100 break-words">{{ $reservaSeleccionada['correo'] ?? '' }}</p>
                    </div>

                    @if(!empty($reservaSeleccionada['tipo_identificacion']))
                    <div>
                        <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Identificación</label>
                        <p class="text-sm dark:text-zinc-100">
                            {{ $reservaSeleccionada['tipo_identificacion'] }}:
                            {{ $reservaSeleccionada['no_identificacion'] ?? '' }}
                        </p>
                    </div>
                    @endif

                    @if(!empty($reservaSeleccionada['edad']))
                    <div>
                        <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Edad</label>
                        <p class="text-sm dark:text-zinc-100">{{ $reservaSeleccionada['edad'] }} años</p>
                    </div>
                    @endif

                    @if(!empty($reservaSeleccionada['direccion']))
                    <div>
                        <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Dirección</label>
                        <p class="text-sm dark:text-zinc-100">{{ $reservaSeleccionada['direccion'] }}</p>
                    </div>
                    @endif

                    @if(!empty($reservaSeleccionada['estado_origen']) || !empty($reservaSeleccionada['pais_origen']))
                    <div>
                        <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Procedencia</label>
                        <p class="text-sm dark:text-zinc-100">
                            {{ $reservaSeleccionada['estado_origen'] ?? '' }}
                            {{ !empty($reservaSeleccionada['estado_origen']) && !empty($reservaSeleccionada['pais_origen']) ? ', ' : '' }}
                            {{ $reservaSeleccionada['pais_origen'] ?? '' }}
                        </p>
                    </div>
                    @endif
                </div>

                <!-- Columna 2 -->
                <div
                    class="space-y-4 bg-green-50 dark:bg-green-900/10 p-4 rounded-lg border border-green-200 dark:border-green-800">
                    <h4 class="text-lg font-semibold text-green-900 dark:text-green-100 flex items-center gap-2">
                        Detalles de la Reserva
                    </h4>

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
                                    @default bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                @endswitch">
                                {{ ucfirst($reservaSeleccionada['estado']) }}
                            </span>
                        </p>
                    </div>

                    @if(!empty($reservaSeleccionada['no_habitacion']))
                    <div class="bg-white dark:bg-zinc-800 p-3 rounded-lg">
                        <label class="text-xs font-medium">Habitación</label>
                        <p class="text-lg font-bold text-green-600 dark:text-green-400">
                            No. {{ $reservaSeleccionada['no_habitacion'] }}
                        </p>
                        <p class="text-xs">{{ $reservaSeleccionada['tipo_habitacion'] }}</p>
                    </div>
                    @endif

                    @if(!empty($reservaSeleccionada['no_espacio']))
                    <div>
                        <label class="text-xs font-medium">Estacionamiento</label>
                        <p class="text-sm">Espacio No. {{ $reservaSeleccionada['no_espacio'] }}</p>
                    </div>
                    @endif

                    @if(!empty($reservaSeleccionada['nombre_plataforma']))
                    <div>
                        <label class="text-xs font-medium">Plataforma</label>
                        <p class="text-sm">{{ $reservaSeleccionada['nombre_plataforma'] }}</p>
                    </div>
                    @endif
                </div>

                <!-- Columna 3 -->
                <div class="space-y-4">
                    <div
                        class="bg-amber-50 dark:bg-amber-900/10 p-4 rounded-lg border border-amber-200 dark:border-amber-800">

                        <h4 class="text-lg font-semibold text-amber-900 dark:text-amber-100 mb-4">
                            Desglose de Costos
                        </h4>

                        <div class="space-y-3">

                            <div class="flex justify-between border-b pb-2">
                                <div>
                                    <span class="text-sm">Habitación</span><br>
                                    <span class="text-xs text-zinc-500">
                                        {{ $reservaSeleccionada['total_dias'] }} noches ×
                                        ${{ number_format($reservaSeleccionada['total_precio_noche'], 2) }}
                                    </span>
                                </div>
                                <span class="font-semibold">
                                    ${{ number_format($reservaSeleccionada['total_subtotal'], 2) }}
                                </span>
                            </div>

                            @if(($reservaSeleccionada['total_comision_porcentaje'] ?? 0) > 0)
                            <div class="flex justify-between text-orange-600 dark:text-orange-400 border-b pb-2">
                                <span class="text-sm">
                                    Comisión {{ $reservaSeleccionada['nombre_plataforma'] }}
                                    <span class="block text-xs">
                                        ({{ $reservaSeleccionada['total_comision_porcentaje'] }}%)
                                    </span>
                                </span>
                                <span class="font-semibold">
                                    ${{ number_format($reservaSeleccionada['total_monto_comision'], 2) }}
                                </span>
                            </div>
                            @endif

                            <div class="flex justify-between text-lg font-bold bg-amber-100 dark:bg-amber-900/30 p-3 rounded-lg">
                                <span>Total:</span>
                                <span class="text-green-600 dark:text-green-400">
                                    ${{ number_format($reservaSeleccionada['total_final'], 2) }}
                                </span>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <div class="bg-zinc-50 dark:bg-zinc-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button wire:click="cerrarModalVer"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:w-auto sm:text-sm">
                Cerrar
            </button>
        </div>

    </div>
</div>
@endif
