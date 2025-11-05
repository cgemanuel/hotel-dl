<!-- Modal Ver Reserva -->
@if($mostrarModalVer && $reservaSeleccionada)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity"
             wire:click="cerrarModalVer"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div class="inline-block align-bottom bg-white dark:bg-zinc-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white dark:bg-zinc-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6 border-b border-zinc-200 dark:border-zinc-700 pb-4">
                    <h3 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                        Detalles de la Reserva #{{ $reservaSeleccionada->idreservas }}
                    </h3>
                    <button wire:click="cerrarModalVer"
                            class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Información del Cliente -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 border-b border-zinc-200 dark:border-zinc-700 pb-2">
                            Información del Cliente
                        </h4>

                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Nombre Completo</label>
                            <p class="text-base text-zinc-900 dark:text-zinc-100">{{ $reservaSeleccionada->nom_completo }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Folio</label>
                            <p class="text-base text-zinc-900 dark:text-zinc-100">{{ $reservaSeleccionada->folio }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Correo</label>
                            <p class="text-base text-zinc-900 dark:text-zinc-100">{{ $reservaSeleccionada->correo }}</p>
                        </div>

                        @if($reservaSeleccionada->tipo_identificacion)
                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Identificación</label>
                            <p class="text-base text-zinc-900 dark:text-zinc-100">
                                {{ $reservaSeleccionada->tipo_identificacion }}: {{ $reservaSeleccionada->no_identificacion }}
                            </p>
                        </div>
                        @endif

                        @if($reservaSeleccionada->edad)
                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Edad</label>
                            <p class="text-base text-zinc-900 dark:text-zinc-100">{{ $reservaSeleccionada->edad }} años</p>
                        </div>
                        @endif

                        @if($reservaSeleccionada->direccion)
                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Dirección</label>
                            <p class="text-base text-zinc-900 dark:text-zinc-100">{{ $reservaSeleccionada->direccion }}</p>
                        </div>
                        @endif

                        @if($reservaSeleccionada->estado_origen || $reservaSeleccionada->pais_origen)
                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Procedencia</label>
                            <p class="text-base text-zinc-900 dark:text-zinc-100">
                                {{ $reservaSeleccionada->estado_origen }}{{ $reservaSeleccionada->estado_origen && $reservaSeleccionada->pais_origen ? ', ' : '' }}{{ $reservaSeleccionada->pais_origen }}
                            </p>
                        </div>
                        @endif
                    </div>

                    <!-- Información de la Reserva -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 border-b border-zinc-200 dark:border-zinc-700 pb-2">
                            Detalles de la Reserva
                        </h4>

                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Check-in</label>
                            <p class="text-base text-zinc-900 dark:text-zinc-100">
                                {{ \Carbon\Carbon::parse($reservaSeleccionada->fecha_check_in)->format('d/m/Y') }}
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Check-out</label>
                            <p class="text-base text-zinc-900 dark:text-zinc-100">
                                {{ \Carbon\Carbon::parse($reservaSeleccionada->fecha_check_out)->format('d/m/Y') }}
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Número de Personas</label>
                            <p class="text-base text-zinc-900 dark:text-zinc-100">{{ $reservaSeleccionada->no_personas }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Estado</label>
                            <div class="mt-1">
                                @php
                                    $estadoClasses = match($reservaSeleccionada->estado) {
                                        'confirmada' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'cancelada' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
                                    };
                                @endphp
                                <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full {{ $estadoClasses }}">
                                    {{ ucfirst($reservaSeleccionada->estado) }}
                                </span>
                            </div>
                        </div>

                        @if($reservaSeleccionada->no_habitacion)
                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Habitación</label>
                            <p class="text-base text-zinc-900 dark:text-zinc-100">
                                No. {{ $reservaSeleccionada->no_habitacion }}
                                @if($reservaSeleccionada->tipo_habitacion)
                                    ({{ $reservaSeleccionada->tipo_habitacion }})
                                @endif
                            </p>
                        </div>
                        @endif

                        @if($reservaSeleccionada->precio)
                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Precio por Noche</label>
                            <p class="text-base text-zinc-900 dark:text-zinc-100">${{ number_format($reservaSeleccionada->precio, 2) }}</p>
                        </div>
                        @endif

                        @if($reservaSeleccionada->no_espacio)
                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Estacionamiento</label>
                            <p class="text-base text-zinc-900 dark:text-zinc-100">Espacio No. {{ $reservaSeleccionada->no_espacio }}</p>
                        </div>
                        @endif

                        @if($reservaSeleccionada->nombre_plataforma)
                        <div>
                            <label class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Plataforma de Reserva</label>
                            <p class="text-base text-zinc-900 dark:text-zinc-100">{{ $reservaSeleccionada->nombre_plataforma }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Desglose de Costos -->
            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4 mt-4">
                <h4 class="text-lg font-semibold mb-3 text-zinc-900 dark:text-zinc-100">
                    💰 Desglose de Costos
                </h4>

                <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4 space-y-3">
                    <!-- Subtotal -->
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-600 dark:text-zinc-400">
                            Habitación ({{ $reservaSeleccionada->total_calculado['dias'] }} {{ $reservaSeleccionada->total_calculado['dias'] == 1 ? 'noche' : 'noches' }} × ${{ number_format($reservaSeleccionada->total_calculado['precio_noche'], 2) }})
                        </span>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">
                            ${{ number_format($reservaSeleccionada->total_calculado['subtotal'], 2) }}
                        </span>
                    </div>

                    <!-- Comisión -->
                    @if($reservaSeleccionada->total_calculado['comision_porcentaje'] > 0)
                        <div class="flex justify-between text-sm text-orange-600 dark:text-orange-400">
                            <span>
                                Comisión {{ $reservaSeleccionada->nombre_plataforma }} ({{ $reservaSeleccionada->total_calculado['comision_porcentaje'] }}%)
                            </span>
                            <span class="font-medium">
                                ${{ number_format($reservaSeleccionada->total_calculado['monto_comision'], 2) }}
                            </span>
                        </div>
                    @endif

                    <!-- Total -->
                    <div class="flex justify-between text-lg font-bold border-t border-zinc-300 dark:border-zinc-600 pt-3">
                        <span class="text-zinc-900 dark:text-zinc-100">Total a Pagar:</span>
                        <span class="text-green-600 dark:text-green-400">
                            ${{ number_format($reservaSeleccionada->total_calculado['total'], 2) }} MXN
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-zinc-50 dark:bg-zinc-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button"
                        wire:click="cerrarModalVer"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endif
