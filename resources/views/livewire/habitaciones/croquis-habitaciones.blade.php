<div class="w-full mx-auto p-6 bg-gradient-to-br from-gray-900 to-gray-800 rounded-lg">
    <h2 class="text-3xl font-bold text-white mb-2">Croquis de Habitaciones</h2>
    <p class="text-gray-400 mb-6">Haz clic en una habitación para ver detalles</p>

    <!-- Tabs de Plantas -->
    <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
        @foreach($plantas as $planta)
            <button
                wire:click="cambiarPlanta('{{ $planta }}')"
                class="px-6 py-2 rounded-lg font-semibold transition-all duration-200 whitespace-nowrap {{ $plantaActiva === $planta ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}"
            >
                {{ $planta }}
            </button>
        @endforeach
    </div>

    <!-- Estadísticas de la Planta -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <p class="text-gray-400 text-sm">Total de Habitaciones</p>
            <p class="text-2xl font-bold text-white mt-2">{{ $totalHabitaciones }}</p>
        </div>
        <div class="bg-green-900 bg-opacity-50 rounded-lg p-4 border border-green-700">
            <p class="text-green-300 text-sm">Disponibles</p>
            <p class="text-2xl font-bold text-green-400 mt-2">{{ $disponibles }}</p>
        </div>
        <div class="bg-red-900 bg-opacity-50 rounded-lg p-4 border border-red-700">
            <p class="text-red-300 text-sm">Ocupadas</p>
            <p class="text-2xl font-bold text-red-400 mt-2">{{ $ocupadas }}</p>
        </div>
        <div class="bg-yellow-900 bg-opacity-50 rounded-lg p-4 border border-yellow-700">
            <p class="text-yellow-300 text-sm">En Mantenimiento</p>
            <p class="text-2xl font-bold text-yellow-400 mt-2">{{ $mantenimiento }}</p>
        </div>
    </div>

    <!-- Grid de Habitaciones - MEJORADO -->
    <div class="min-h-[400px] mb-8">
        @if(count($habitacionesActuales) > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                @foreach($habitacionesActuales as $habitacion)
                    @php
                        $colores = [
                            'disponible' => 'bg-green-500 hover:bg-green-600',
                            'ocupada' => 'bg-red-500 hover:bg-red-600',
                            'mantenimiento' => 'bg-yellow-500 hover:bg-yellow-600',
                        ];
                        $colorClase = $colores[$habitacion->estado] ?? 'bg-gray-500';
                    @endphp
                    <button
                        wire:click="seleccionarHabitacion({{ $habitacion->idhabitacion }})"
                        class="p-4 rounded-lg transition-all duration-200 transform hover:scale-105 hover:shadow-lg flex flex-col items-center justify-center text-white font-bold {{ $colorClase }} min-h-[120px]"
                    >
                        <div class="text-xl">Hab. {{ $habitacion->no_habitacion }}</div>
                        <div class="text-xs opacity-80 capitalize mt-1">{{ $habitacion->tipo }}</div>
                        <div class="text-xs opacity-80 capitalize mt-1">{{ $habitacion->estado }}</div>
                    </button>
                @endforeach
            </div>
        @else
            <div class="text-center text-gray-400 py-12">
                <p class="text-xl">No hay habitaciones en esta planta</p>
            </div>
        @endif
    </div>

    <!-- Leyenda -->
    <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
        <p class="text-gray-300 text-sm font-semibold mb-3">Leyenda:</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-green-500 rounded"></div>
                <span class="text-gray-300 text-sm">Disponible</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-red-500 rounded"></div>
                <span class="text-gray-300 text-sm">Ocupada</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-yellow-500 rounded"></div>
                <span class="text-gray-300 text-sm">En Mantenimiento</span>
            </div>
        </div>
    </div>

    <!-- Modal de Detalles -->
    @if($mostrarModal && $habitacionSeleccionada)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
             wire:click.self="cerrarModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                        Habitación {{ $habitacionSeleccionada['no_habitacion'] }}
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

                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tipo</p>
                        <p class="font-semibold text-gray-900 dark:text-white mt-1 capitalize">
                            {{ $habitacionSeleccionada['tipo'] }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Precio por Noche</p>
                        <p class="font-semibold text-gray-900 dark:text-white mt-1">
                            ${{ number_format($habitacionSeleccionada['precio'], 2) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Estado</p>
                        <div class="flex items-center gap-2 mt-1">
                            @php
                                $colorBg = match($habitacionSeleccionada['estado']) {
                                    'disponible' => 'bg-green-500',
                                    'ocupada' => 'bg-red-500',
                                    'mantenimiento' => 'bg-yellow-500',
                                    default => 'bg-gray-500'
                                };
                            @endphp
                            <div class="w-4 h-4 rounded {{ $colorBg }}"></div>
                            <p class="font-semibold text-gray-900 dark:text-white capitalize">
                                {{ $habitacionSeleccionada['estado'] }}
                            </p>
                        </div>
                    </div>

                    @if($habitacionSeleccionada['estado'] === 'ocupada' && isset($habitacionSeleccionada['nom_completo']))
                        <hr class="dark:border-gray-700">

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Cliente</p>
                            <p class="font-semibold text-gray-900 dark:text-white mt-1">
                                {{ $habitacionSeleccionada['nom_completo'] }}
                            </p>
                        </div>

                        @if(isset($habitacionSeleccionada['telefono']))
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Teléfono</p>
                            <p class="font-semibold text-gray-900 dark:text-white mt-1">
                                {{ $habitacionSeleccionada['telefono'] }}
                            </p>
                        </div>
                        @endif

                        @if(isset($habitacionSeleccionada['correo']))
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Correo</p>
                            <p class="font-semibold text-gray-900 dark:text-white mt-1 break-all">
                                {{ $habitacionSeleccionada['correo'] }}
                            </p>
                        </div>
                        @endif

                        @if(isset($habitacionSeleccionada['no_personas']))
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Número de Personas</p>
                            <p class="font-semibold text-gray-900 dark:text-white mt-1">
                                {{ $habitacionSeleccionada['no_personas'] }}
                            </p>
                        </div>
                        @endif

                        @if(isset($habitacionSeleccionada['fecha_check_in']))
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Check-in</p>
                            <p class="font-semibold text-gray-900 dark:text-white mt-1">
                                {{ \Carbon\Carbon::parse($habitacionSeleccionada['fecha_check_in'])->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        @endif

                        @if(isset($habitacionSeleccionada['fecha_check_out']))
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Check-out</p>
                            <p class="font-semibold text-gray-900 dark:text-white mt-1">
                                {{ \Carbon\Carbon::parse($habitacionSeleccionada['fecha_check_out'])->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        @endif
                    @elseif($habitacionSeleccionada['estado'] === 'disponible')
                        <p class="text-center py-4 text-green-600 dark:text-green-400 font-semibold">
                            Habitación disponible para reservar
                        </p>
                    @elseif($habitacionSeleccionada['estado'] === 'mantenimiento')
                        <p class="text-center py-4 text-yellow-600 dark:text-yellow-400 font-semibold">
                            En mantenimiento
                        </p>
                    @endif
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 rounded-b-lg sticky bottom-0">
                    <button
                        wire:click="cerrarModal"
                        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
