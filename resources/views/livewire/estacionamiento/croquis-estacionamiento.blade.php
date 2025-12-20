<div class="w-full max-w-5xl mx-auto p-6 bg-gradient-to-br from-green-900 to-green-800 rounded-lg">
    <h2 class="text-3xl font-bold text-white mb-2">Croquis de Estacionamiento</h2>
    <p class="text-gray-300 mb-8">Haz clic en un espacio para ver detalles y cambiar disponibilidad</p>

    <!-- Croquis Principal -->
    <div class="flex gap-12 justify-center items-start mb-8">
        <!-- Lado Izquierdo -->
        <div class="flex flex-col gap-3">
            @foreach($espaciosIzquierda as $espacio)
                @php
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

        <!-- Centro - Representación visual -->
        <div class="hidden md:flex items-center justify-center">
            <div class="w-24 h-64 border-4 border-amber-500 rounded-lg bg-green-700 opacity-50 flex items-center justify-center">
                <span class="text-amber-300 text-center text-sm font-bold">Entrada</span>
            </div>
        </div>

        <!-- Lado Derecho -->
        <div class="flex flex-col gap-3">
            @foreach($espaciosDerecha as $espacio)
                @php
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800 z-10">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                        Espacio {{ $espacioSeleccionado['numero'] }}
                    </h3>
                    <button
                        wire:click="cerrarModal"
                        class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6">

                    <!-- Grid principal de 3 columnas -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <!-- Columna 1: Estado y Controles -->
                        <div class="space-y-4">
                            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Estado
                                </h4>
                                
                                <div class="flex items-center gap-2 mb-4">
                                    @php
                                        $colorBg = match($espacioSeleccionado['estado']) {
                                            'disponible' => 'bg-green-500',
                                            'ocupado' => 'bg-red-500',
                                            default => 'bg-gray-500'
                                        };
                                    @endphp
                                    <div class="w-4 h-4 rounded {{ $colorBg }}"></div>
                                    <p class="font-semibold text-gray-900 dark:text-white capitalize">
                                        {{ $espacioSeleccionado['estado'] }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Cambiar Estado:</p>
                                    <button
                                        wire:click="cambiarEstadoEspacio({{ $espacioSeleccionado['numero'] }}, 'disponible')"
                                        class="w-full px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors text-xs"
                                        wire:confirm="¿Desea cambiar el estado a Disponible?"
                                    >
                                        Marcar Disponible
                                    </button>
                                    <button
                                        wire:click="cambiarEstadoEspacio({{ $espacioSeleccionado['numero'] }}, 'ocupado')"
                                        class="w-full px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors text-xs"
                                        wire:confirm="¿Desea cambiar el estado a Ocupado?"
                                    >
                                        Marcar Ocupado
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Columna 2: Información de la Reserva -->
                        @if($espacioSeleccionado['estado'] === 'ocupado' && $espacioSeleccionado['nom_completo'])
                            <div class="space-y-4">
                                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                                    <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-3 flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Reserva
                                    </h4>

                                    <div class="space-y-3">
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Cliente</p>
                                            <p class="font-semibold text-sm text-gray-900 dark:text-white mt-1">
                                                {{ $espacioSeleccionado['nom_completo'] }}
                                            </p>
                                        </div>

                                        @if(isset($espacioSeleccionado['telefono']))
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Teléfono</p>
                                            <p class="font-semibold text-sm text-gray-900 dark:text-white mt-1">
                                                {{ $espacioSeleccionado['telefono'] }}
                                            </p>
                                        </div>
                                        @endif

                                        <div class="grid grid-cols-2 gap-3 pt-2 border-t border-blue-200 dark:border-blue-700">
                                            <div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Check-in</p>
                                                <p class="font-semibold text-sm text-gray-900 dark:text-white mt-1">
                                                    {{ \Carbon\Carbon::parse($espacioSeleccionado['fecha_check_in'])->format('d/m/Y') }}
                                                </p>
                                            </div>

                                            <div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Check-out</p>
                                                <p class="font-semibold text-sm text-gray-900 dark:text-white mt-1">
                                                    {{ \Carbon\Carbon::parse($espacioSeleccionado['fecha_check_out'])->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        {{-- 🔥 NUEVA SECCIÓN: Datos del Vehículo --}}
                        @php
                            $reservaConVehiculo = DB::table('reservas')
                                ->where('estacionamiento_no_espacio', $espacioSeleccionado['numero'])
                                ->where('estado', 'confirmada')
                                ->whereNotNull('tipo_vehiculo')
                                ->first();
                        @endphp

                        @if($reservaConVehiculo)
                            <hr class="dark:border-gray-700">

                            <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg border border-amber-200 dark:border-amber-800">
                                <h4 class="font-semibold text-amber-900 dark:text-amber-100 mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Información del Vehículo
                                </h4>

                                <div class="space-y-3">
                                    @if($reservaConVehiculo->tipo_vehiculo)
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Tipo de Vehículo</p>
                                        <p class="font-semibold text-gray-900 dark:text-white mt-1">
                                            {{ $reservaConVehiculo->tipo_vehiculo }}
                                        </p>
                                    </div>
                                    @endif

                                    @if($reservaConVehiculo->descripcion_vehiculo)
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Descripción</p>
                                        <p class="text-sm text-gray-900 dark:text-white mt-1 bg-white dark:bg-gray-800 p-3 rounded border border-amber-200 dark:border-amber-700">
                                            {{ $reservaConVehiculo->descripcion_vehiculo }}
                                        </p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                    @elseif($espacioSeleccionado['estado'] === 'disponible')
                        <p class="text-center py-4 text-green-600 dark:text-green-400 font-semibold">
                            Espacio disponible para reservar
                        </p>
                    @endif
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 rounded-b-lg">
                    <button
                        wire:click="cerrarModal"
                        class="w-full px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium transition-colors"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
