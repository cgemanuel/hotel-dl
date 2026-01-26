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
        <p class="text-gray-600 dark:text-gray-300 mb-6">Haz clic en una habitación para ver detalles</p>

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
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Estado de Habitaciones</h3>
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

    {{-- Cuadrícula de habitaciones (Mapa Visual) --}}
    <div class="min-h-[400px] mb-8">
        @if(count($habitacionesActuales) > 0)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 px-4">
                @foreach($habitacionesActuales as $habitacion)
                    @php
                        $estadoNormalizado = strtolower(str_replace(' ', '_', trim($habitacion->estado ?? '')));

                        if ($estadoNormalizado === 'disponible') {
                            $bgClase = 'bg-green-800 hover:bg-green-900';
                            $borderClase = 'border-green-400';
                            $textClase = 'text-white';
                            $tagClase = 'bg-green-100 text-green-800';
                        } elseif ($estadoNormalizado === 'ocupada') {
                            $bgClase = 'bg-red-800 hover:bg-red-700';
                            $borderClase = 'border-red-400';
                            $textClase = 'text-white';
                            $tagClase = 'bg-red-100 text-red-800';
                        } elseif (in_array($estadoNormalizado, ['en_mantenimiento', 'mantenimiento'])) {
                            $bgClase = 'bg-yellow-500 hover:bg-yellow-600';
                            $borderClase = 'border-yellow-400';
                            $textClase = 'text-white';
                            $tagClase = 'bg-yellow-100 text-yellow-800';
                        } else {
                            $bgClase = 'bg-gray-600 hover:bg-gray-700';
                            $borderClase = 'border-gray-400';
                            $textClase = 'text-white';
                            $tagClase = 'bg-gray-100 text-gray-800';
                        }
                    @endphp

                    <button
                        wire:click="seleccionarHabitacion({{ $habitacion->idhabitacion }})"
                        class="w-full h-72 rounded-2xl overflow-hidden shadow-xl border-4 {{ $bgClase }} {{ $borderClase }} {{ $textClase }} transition-all duration-500 transform hover:scale-105 hover:shadow-2xl"
                    >
                        <div class="h-full flex flex-col items-center justify-center p-6">
                            <span class="text-5xl font-bold mb-4">{{ $habitacion->no_habitacion }}</span>
                            <h3 class="text-2xl font-semibold capitalize mb-2">{{ $habitacion->tipo }}</h3>
                            <span class="mt-4 px-4 py-2 rounded-full text-sm font-medium capitalize {{ $tagClase }}">
                                {{ str_replace('_', ' ', $habitacion->estado) }}
                            </span>
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
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Estado Actual</p>
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

                        {{-- Botones para cambiar estado --}}
                        <div class="flex flex-col gap-2 mt-4">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cambiar Estado:</p>
                            <button
                                wire:click="cambiarEstadoHabitacion({{ $habitacionSeleccionada['idhabitacion'] }}, 'disponible')"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors text-sm"
                                wire:confirm="¿Desea cambiar el estado a Disponible?"
                            >
                                Marcar como Disponible
                            </button>
                            <button
                                wire:click="cambiarEstadoHabitacion({{ $habitacionSeleccionada['idhabitacion'] }}, 'ocupada')"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors text-sm"
                                wire:confirm="¿Desea cambiar el estado a Ocupada?"
                            >
                                Marcar como Ocupada
                            </button>
                            <button
                                wire:click="cambiarEstadoHabitacion({{ $habitacionSeleccionada['idhabitacion'] }}, 'en_mantenimiento')"
                                class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg font-medium transition-colors text-sm"
                                wire:confirm="¿Desea cambiar el estado a Mantenimiento?"
                            >
                                Marcar en Mantenimiento
                            </button>
                        </div>
                    </div>

                    {{-- Información del cliente si está ocupada --}}
                    @if($habitacionSeleccionada['estado'] === 'ocupada' && isset($habitacionSeleccionada['nom_completo']))
                        <hr class="dark:border-gray-700">

                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Reserva Actual</h4>

                            @if(isset($habitacionSeleccionada['folio']))
                            <div class="mb-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Folio</p>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ $habitacionSeleccionada['folio'] }}
                                </p>
                            </div>
                            @endif

                            <div class="mb-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Cliente</p>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ $habitacionSeleccionada['nom_completo'] }}
                                </p>
                            </div>

                            @if(isset($habitacionSeleccionada['correo']))
                            <div class="mb-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Correo</p>
                                <p class="font-semibold text-gray-900 dark:text-white break-all">
                                    {{ $habitacionSeleccionada['correo'] }}
                                </p>
                            </div>
                            @endif

                            @if(isset($habitacionSeleccionada['no_personas']))
                            <div class="mb-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Número de Personas</p>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ $habitacionSeleccionada['no_personas'] }}
                                </p>
                            </div>
                            @endif

                            @if(isset($habitacionSeleccionada['fecha_check_in']))
                            <div class="mb-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Check-in</p>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($habitacionSeleccionada['fecha_check_in'])->format('d/m/Y') }}
                                </p>
                            </div>
                            @endif

                            @if(isset($habitacionSeleccionada['fecha_check_out']))
                            <div class="mb-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Check-out</p>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($habitacionSeleccionada['fecha_check_out'])->format('d/m/Y') }}
                                </p>
                            </div>
                            @endif
                        </div>
                    @elseif($habitacionSeleccionada['estado'] === 'disponible')
                        <p class="text-center py-4 text-green-600 dark:text-green-400 font-semibold">
                            Habitación disponible para reservar
                        </p>
                    @elseif($habitacionSeleccionada['estado'] === 'en_mantenimiento')
                        <p class="text-center py-4 text-yellow-600 dark:text-yellow-400 font-semibold">
                            En mantenimiento
                        </p>
                    @endif

                    <hr class="dark:border-gray-700">

                    {{-- Botón para ver historial --}}
                    <button
                        wire:click="toggleHistorial"
                        class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $mostrarHistorial ? 'Ocultar Historial' : 'Ver Historial de Reservas' }}
                    </button>

                    {{-- Mini tabla de historial --}}
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
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Folio</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cliente</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Check-in</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Check-out</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($historialReservas as $reserva)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-white">
                                                    {{ $reserva->folio ?? 'N/A' }}
                                                </td>
                                                <td class="px-3 py-2 text-sm">
                                                    <div class="text-gray-900 dark:text-white font-medium">
                                                        {{ $reserva->nom_completo }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $reserva->correo ?? $reserva->telefono }}
                                                    </div>
                                                </td>
                                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-white">
                                                    {{ \Carbon\Carbon::parse($reserva->fecha_check_in)->format('d/m/Y') }}
                                                </td>
                                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-white">
                                                    {{ \Carbon\Carbon::parse($reserva->fecha_check_out)->format('d/m/Y') }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                                        {{ $reserva->estado === 'confirmada' ? 'bg-green-100 text-green-800' : '' }}
                                                        {{ $reserva->estado === 'completada' ? 'bg-blue-100 text-blue-800' : '' }}
                                                        {{ $reserva->estado === 'cancelada' ? 'bg-red-100 text-red-800' : '' }}
                                                        {{ $reserva->estado === 'pendiente' ? 'bg-yellow-100 text-yellow-800' : '' }}">
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
