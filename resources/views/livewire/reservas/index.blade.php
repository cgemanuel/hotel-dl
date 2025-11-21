<div class="p-6 lg:p-8 bg-white dark:bg-zinc-900">
    <div class="mb-6 border-b-4 border-green-700 pb-4">
        <flux:heading size="xl" class="mb-2 text-green-900 dark:text-green-100">Gestión de Reservas</flux:heading>
        <flux:subheading class="text-gray-600 dark:text-gray-400">Administra las reservas de hotel desde este panel.</flux:subheading>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/20 border-l-4 border-green-600 text-green-800 dark:text-green-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('message') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/20 border-l-4 border-red-600 text-red-800 dark:text-red-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <div class="mb-6 bg-green-50 dark:bg-green-900/10 p-4 rounded-lg border-2 border-green-200 dark:border-green-800">
        <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-4">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            Filtros de Búsqueda
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-green-800 dark:text-green-200 mb-1">Buscar</label>
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Folio, nombre o ID..."
                    icon="magnifying-glass"
                    class="w-full"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-green-800 dark:text-green-200 mb-1">Fecha Inicio</label>
                <input type="date"
                       wire:model.live="fecha_inicio"
                       class="w-full px-3 py-2 border border-green-300 dark:border-green-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-green-800 dark:text-green-200 mb-1">Fecha Fin</label>
                <input type="date"
                       wire:model.live="fecha_fin"
                       class="w-full px-3 py-2 border border-green-300 dark:border-green-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-green-800 dark:text-green-200 mb-1">Estado</label>
                <select wire:model.live="estado_filtro"
                         class="w-full px-3 py-2 border border-green-300 dark:border-green-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500">
                    <option value="">Todos</option>
                    <option value="confirmada">Confirmada</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="cancelada">Cancelada</option>
                    <option value="completada">Completada</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button wire:click="limpiarFiltros"
                        class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors text-sm">
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="text-sm text-gray-600 dark:text-gray-400">
            Mostrando resultados filtrados
        </div>
        <flux:button variant="primary" icon="plus" wire:click="$dispatch('abrirModal')" class="bg-amber-600 hover:bg-amber-700">
            Nueva Reserva
        </flux:button>
    </div>

    @livewire('reservas.crear-reserva')

    @include('livewire.reservas.modal-ver', [
        'mostrarModalVer' => $mostrarModalVer,
        'reservaSeleccionada' => $reservaSeleccionada
    ])

    @include('livewire.reservas.modal-editar', [
        'mostrarModalEditar' => $mostrarModalEditar
    ])

    <div class="overflow-x-auto bg-white dark:bg-zinc-900 rounded-lg border-2 border-green-200 dark:border-green-800 shadow-lg">
        <table class="min-w-full divide-y divide-green-200 dark:divide-green-800">
            <thead class="bg-green-800 dark:bg-green-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Folio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Habitación</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Check-in</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Check-out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Personas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Estacionamiento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($reservas as $reserva)
                <tr class="hover:bg-green-50 dark:hover:bg-green-900/10 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-bold text-amber-700 dark:text-amber-400">
                            {{ $reserva->folio ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $reserva->nom_completo }}</span>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $reserva->correo ?? $reserva->telefono }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            Hab. {{ $reserva->no_habitacion }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                        {{ \Carbon\Carbon::parse($reserva->fecha_check_in)->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                        {{ \Carbon\Carbon::parse($reserva->fecha_check_out)->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100 text-center">
                        {{ $reserva->no_personas }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <button
                            wire:click="asignarEstacionamiento({{ $reserva->idreservas }})"
                            wire:loading.attr="disabled"
                            class="px-3 py-1 text-xs font-medium rounded-lg transition-colors disabled:opacity-50
                                {{ $reserva->estacionamiento_no_espacio
                                    ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-800'
                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700'
                                }}">
                            <span wire:loading.remove wire:target="asignarEstacionamiento({{ $reserva->idreservas }})">
                                {{ $reserva->estacionamiento_no_espacio ? 'Espacio ' . $reserva->estacionamiento_no_espacio : 'Asignar' }}
                            </span>
                            <span wire:loading wire:target="asignarEstacionamiento({{ $reserva->idreservas }})">
                                Cargando...
                            </span>
                        </button>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $estadoClasses = match($reserva->estado) {
                                'confirmada' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                'cancelada' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                'completada' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
                            };
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $estadoClasses }}">
                            {{ ucfirst($reserva->estado) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="font-bold text-lg text-amber-700 dark:text-amber-400">
                            ${{ number_format($reserva->total_calculado['total'], 2) }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex flex-col gap-1">
                            <button wire:click="ver({{ $reserva->idreservas }})"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                Ver
                            </button>
                            @if($reserva->estado != 'completada' && $reserva->estado != 'cancelada')
                            <button type="button" wire:click.stop="editar({{ $reserva->idreservas }})"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                Editar
                            </button>
                            @endif
                            @if($reserva->estado == 'confirmada' || $reserva->estado == 'pendiente')
                            <button wire:click="eliminar({{ $reserva->idreservas }})"
                                    wire:confirm="¿Estás seguro de cancelar esta reserva?"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-medium">
                                Cancelar
                            </button>
                            <button wire:click="liberar({{ $reserva->idreservas }})"
                                    wire:confirm="¿Desea liberar esta reserva? Las habitaciones y estacionamiento quedarán disponibles."
                                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 font-medium">
                                Liberar
                            </button>
                            @endif
                            @if(auth()->user()->rol === 'gerente')
                            <button wire:click="eliminarPermanente({{ $reserva->idreservas }})"
                                wire:confirm="⚠️ ADVERTENCIA: Esto eliminará la reserva PERMANENTEMENTE de la base de datos. ¿Continuar?"
                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-medium">
                            Eliminar (Permanente)
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                        <svg class="mx-auto h-12 w-12 text-zinc-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm">No hay reservas registradas con los filtros aplicados</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Debug: Mostrar estado del modal --}}
    @if($mostrarModalEstacionamiento)
        <div class="fixed top-4 right-4 bg-yellow-500 text-black px-4 py-2 rounded z-50">
            Modal activo - Espacios: {{ count($espacios_disponibles) }}
        </div>
    @endif

    {{-- Modal de Estacionamiento --}}
    @if($mostrarModalEstacionamiento)
    <div class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-estacionamiento" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

            {{-- Overlay oscuro --}}
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 dark:bg-black dark:bg-opacity-90 transition-opacity"
                aria-hidden="true"
                wire:click="cerrarModalEstacionamiento"></div>

            {{-- Espaciador para centrar --}}
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Contenedor del modal --}}
            <div class="inline-block align-bottom bg-white dark:bg-zinc-900 rounded-lg text-left overflow-visible shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative"
                wire:click.stop>

                {{-- Header --}}
                <div class="bg-white dark:bg-zinc-900 px-6 pt-6 pb-4 border-b border-gray-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="modal-estacionamiento">
                            Asignar Espacio de Estacionamiento
                        </h3>
                        <button wire:click="cerrarModalEstacionamiento"
                                type="button"
                                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Body --}}
                <div class="bg-white dark:bg-zinc-900 px-6 py-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Selecciona un espacio disponible
                    </label>

                    <select wire:model="espacio_seleccionado"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Sin estacionamiento</option>
                        @forelse($espacios_disponibles as $espacio)
                            <option value="{{ $espacio->no_espacio }}">
                                Espacio {{ $espacio->no_espacio }}
                                @if($espacio->estado === 'disponible')
                                    - Disponible
                                @else
                                    - Asignado actualmente
                                @endif
                            </option>
                        @empty
                            <option disabled>No hay espacios disponibles</option>
                        @endforelse
                    </select>

                    <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                        <p>Total de espacios: {{ count($espacios_disponibles) }}</p>
                        @if($espacio_seleccionado)
                            <p class="text-green-600 dark:text-green-400">Espacio seleccionado: {{ $espacio_seleccionado }}</p>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 dark:bg-zinc-800 px-6 py-4 flex flex-row-reverse gap-3">
                    <button type="button"
                            wire:click="guardarEstacionamiento"
                            wire:loading.attr="disabled"
                            class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="guardarEstacionamiento">Guardar</span>
                        <span wire:loading wire:target="guardarEstacionamiento">Guardando...</span>
                    </button>
                    <button type="button"
                            wire:click="cerrarModalEstacionamiento"
                            class="inline-flex justify-center rounded-md border border-gray-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="mt-6">
        {{ $reservas->links() }}
    </div>
</div>
