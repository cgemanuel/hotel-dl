<div class="w-full max-w-5xl mx-auto p-6 bg-gradient-to-br from-green-900 to-green-800 rounded-lg">
    <h2 class="text-3xl font-bold text-white mb-2">Croquis de Estacionamiento</h2>
    <p class="text-gray-300 mb-8">Haz clic en un espacio para ver detalles y cambiar disponibilidad</p>

    <!-- Croquis Principal -->
    <div class="flex gap-12 justify-center items-start mb-8">
        <!-- Lado Izquierdo -->
        <div class="flex flex-col gap-3">
            @foreach($espaciosIzquierda as $espacio)
                @php
                    $espacio = (object) $espacio;
                    $colores = [
                        'disponible' => 'bg-green-600 hover:bg-green-700',
                        'ocupado' => 'bg-red-600 hover:bg-red-700',
                    ];
                    $colorClase = $colores[$espacio->estado] ?? 'bg-gray-500';
                @endphp
                <button
                    wire:click="seleccionarEspacio({{ $espacio->numero }})"
                    class="w-32 h-20 rounded-lg transition-all duration-200 transform hover:scale-105 hover:shadow-lg flex flex-col items-center justify-center text-white font-bold text-sm cursor-pointer {{ $colorClase }}"
                >
                    <div>Espacio {{ $espacio->numero }}</div>
                    <div class="text-xs opacity-80 capitalize">{{ $espacio->estado }}</div>
                </button>
            @endforeach
        </div>

        <!-- Centro -->
        <div class="hidden md:flex items-center justify-center">
            <div class="w-24 h-64 border-4 border-amber-500 rounded-lg bg-green-700 opacity-50 flex items-center justify-center">
                <span class="text-amber-300 text-center text-sm font-bold">Entrada</span>
            </div>
        </div>

        <!-- Lado Derecho -->
        <div class="flex flex-col gap-3">
            @foreach($espaciosDerecha as $espacio)
                @php
                    $espacio = (object) $espacio;
                    $colores = [
                        'disponible' => 'bg-green-600 hover:bg-green-700',
                        'ocupado' => 'bg-red-600 hover:bg-red-700',
                    ];
                    $colorClase = $colores[$espacio->estado] ?? 'bg-gray-500';
                @endphp
                <button
                    wire:click="seleccionarEspacio({{ $espacio->numero }})"
                    class="w-32 h-20 rounded-lg transition-all duration-200 transform hover:scale-105 hover:shadow-lg flex flex-col items-center justify-center text-white font-bold text-sm cursor-pointer {{ $colorClase }}"
                >
                    <div>Espacio {{ $espacio->numero }}</div>
                    <div class="text-xs opacity-80 capitalize">{{ $espacio->estado }}</div>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Modal de Detalles -->
    @if($mostrarModal && $espacioSeleccionado)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
            @click.self="@this.call('cerrarModal')">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] overflow-y-auto">

                <!-- Header Modal -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800 z-10">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                        Espacio {{ $espacioSeleccionado['numero'] }}
                    </h3>
                    <button wire:click="cerrarModal" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <!-- FILA HORIZONTAL DE TARJETAS -->
                    <div class="flex gap-6 overflow-x-auto pb-4">

                        <!-- Tarjeta 1: Estado y Controles -->
                        <div class="flex-shrink-0 w-80">
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg border-2 border-blue-300 dark:border-blue-700 h-full">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2 text-lg">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Estado
                                </h4>

                                <div class="flex items-center gap-3 mb-6">
                                    @php
                                        $colorBg = match($espacioSeleccionado['estado']) {
                                            'disponible' => 'bg-green-500',
                                            'ocupado' => 'bg-red-500',
                                            default => 'bg-gray-500'
                                        };
                                    @endphp
                                    <div class="w-6 h-6 rounded-full {{ $colorBg }}"></div>
                                    <p class="font-bold text-lg text-gray-900 dark:text-white capitalize">
                                        {{ $espacioSeleccionado['estado'] }}
                                    </p>
                                </div>

                                <div class="space-y-3">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Cambiar Estado:</p>
                                    <button
                                        wire:click="cambiarEstadoEspacio({{ $espacioSeleccionado['numero'] }}, 'disponible')"
                                        class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors text-sm"
                                        wire:confirm="¿Desea cambiar el estado a Disponible?"
                                    >
                                        Marcar Disponible
                                    </button>
                                    <button
                                        wire:click="cambiarEstadoEspacio({{ $espacioSeleccionado['numero'] }}, 'ocupado')"
                                        class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors text-sm"
                                        wire:confirm="¿Desea cambiar el estado a Ocupado?"
                                    >
                                        Marcar Ocupado
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta 2: Información de la Reserva -->
                        @if($espacioSeleccionado['estado'] === 'ocupado' && $espacioSeleccionado['nom_completo'])
                            <div class="flex-shrink-0 w-80">
                                <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg border-2 border-blue-300 dark:border-blue-700 h-full">
                                    <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-4 flex items-center gap-2 text-lg">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Reserva
                                    </h4>

                                    <div class="space-y-4">
                                        <div>
                                            <p class="text-xs text-blue-700 dark:text-blue-300 font-medium">Cliente</p>
                                            <p class="font-bold text-base text-gray-900 dark:text-white mt-1">
                                                {{ $espacioSeleccionado['nom_completo'] }}
                                            </p>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4 pt-3 border-t-2 border-blue-200 dark:border-blue-700">
                                            <div>
                                                <p class="text-xs text-blue-700 dark:text-blue-300 font-medium">Check-in</p>
                                                <p class="font-bold text-sm text-gray-900 dark:text-white mt-1">
                                                    {{ \Carbon\Carbon::parse($espacioSeleccionado['fecha_check_in'])->format('d/m/Y') }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-blue-700 dark:text-blue-300 font-medium">Check-out</p>
                                                <p class="font-bold text-sm text-gray-900 dark:text-white mt-1">
                                                    {{ \Carbon\Carbon::parse($espacioSeleccionado['fecha_check_out'])->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tarjeta 3: Vehículo -->
                            @php
                                $reservaConVehiculo = DB::table('reservas')
                                    ->where('estacionamiento_no_espacio', $espacioSeleccionado['numero'])
                                    ->where('estado', 'confirmada')
                                    ->whereNotNull('tipo_vehiculo')
                                    ->first();
                            @endphp

                            @if($reservaConVehiculo)
                                <div class="flex-shrink-0 w-80">
                                    <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg border-2 border-blue-300 dark:border-blue-700 h-full">
                                        <h4 class="font-semibold text-amber-900 dark:text-amber-100 mb-4 flex items-center gap-2 text-lg">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Vehículo
                                        </h4>

                                        <div class="space-y-4">
                                            @if($reservaConVehiculo->tipo_vehiculo)
                                            <div>
                                                <p class="text-xs text-amber-700 dark:text-amber-300 font-medium">Tipo de Vehículo</p>
                                                <p class="font-bold text-base text-gray-900 dark:text-white mt-1">
                                                    {{ $reservaConVehiculo->tipo_vehiculo }}
                                                </p>
                                            </div>
                                            @endif

                                            @if($reservaConVehiculo->descripcion_vehiculo)
                                            <div>
                                                <p class="text-xs text-amber-700 dark:text-amber-300 font-medium mb-2">Descripción</p>
                                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border-2 border-amber-200 dark:border-amber-700">
                                                    <p class="text-sm text-gray-900 dark:text-white leading-relaxed">
                                                        {{ $reservaConVehiculo->descripcion_vehiculo }}
                                                    </p>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Tarjeta 4: Habitaciones -->
                            @if(!empty($espacioSeleccionado['habitaciones']))
                                <div class="flex-shrink-0 w-80">
                                    <div class="bg-purple-50 dark:bg-purple-900/20 p-6 rounded-lg border-2 border-purple-300 dark:border-purple-700 h-full">
                                        <h4 class="font-semibold text-purple-900 dark:text-purple-100 mb-4 flex items-center gap-2 text-lg">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                            </svg>
                                            Habitación(es)
                                        </h4>
                                        <div class="space-y-3">
                                            @foreach($espacioSeleccionado['habitaciones'] as $hab)
                                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-purple-200 dark:border-purple-700 flex items-center gap-3">
                                                    <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                        <span class="text-white font-bold text-sm">{{ $hab['no_habitacion'] }}</span>
                                                    </div>
                                                    <div>
                                                        <p class="font-bold text-gray-900 dark:text-white text-sm">No. {{ $hab['no_habitacion'] }}</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ $hab['tipo'] }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                        @elseif($espacioSeleccionado['estado'] === 'disponible')
                            <div class="flex-shrink-0 w-80">
                                <div class="bg-green-50 dark:bg-green-900/20 p-6 rounded-lg border-2 border-green-300 dark:border-green-700 h-full flex items-center justify-center">
                                    <div class="text-center">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="text-lg font-bold text-green-700 dark:text-green-400">
                                            Espacio disponible para reservar
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 rounded-b-lg border-t-2 border-gray-200 dark:border-gray-600">
                    <button
                        wire:click="cerrarModal"
                        class="w-full px-4 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-bold transition-colors text-base"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
