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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
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

                <div class="p-6 space-y-4">
                    <!-- Estado -->
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Estado Actual</p>
                        <div class="flex items-center gap-2 mb-3">
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

                        <!-- Botones para cambiar estado -->
                        <div class="flex flex-col gap-2 mt-4">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cambiar Estado:</p>
                            <button
                                wire:click="cambiarEstadoEspacio({{ $espacioSeleccionado['numero'] }}, 'disponible')"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors text-sm"
                                wire:confirm="¿Desea cambiar el estado a Disponible?"
                            >
                                Marcar como Disponible
                            </button>
                            <button
                                wire:click="cambiarEstadoEspacio({{ $espacioSeleccionado['numero'] }}, 'ocupado')"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors text-sm"
                                wire:confirm="¿Desea cambiar el estado a Ocupado?"
                            >
                                Marcar como Ocupado
                            </button>
                        </div>
                    </div>

                    <!-- Información del Cliente (si está ocupado) -->
                    @if($espacioSeleccionado['estado'] === 'ocupado' && $espacioSeleccionado['nom_completo'])
                        <hr class="dark:border-gray-700">

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Cliente</p>
                            <p class="font-semibold text-gray-900 dark:text-white mt-1">
                                {{ $espacioSeleccionado['nom_completo'] }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Check-in</p>
                            <p class="font-semibold text-gray-900 dark:text-white mt-1">
                                {{ \Carbon\Carbon::parse($espacioSeleccionado['fecha_check_in'])->format('d/m/Y') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Check-out</p>
                            <p class="font-semibold text-gray-900 dark:text-white mt-1">
                                {{ \Carbon\Carbon::parse($espacioSeleccionado['fecha_check_out'])->format('d/m/Y') }}
                            </p>
                        </div>
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
