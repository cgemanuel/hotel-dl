<div class="p-6 lg:p-8 bg-white dark:bg-zinc-900">
    <div class="mb-6 border-b-4 border-green-700 pb-4">
        <flux:heading size="xl" class="mb-2 text-green-900 dark:text-green-100">Servicios Adicionales</flux:heading>
        <flux:subheading class="text-gray-600 dark:text-gray-400">Registra servicios adicionales solicitados por los huéspedes.</flux:subheading>
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
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 bg-green-50 dark:bg-green-900/10 p-4 rounded-lg border-2 border-green-200 dark:border-green-800">
        <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-4">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            Filtros de Búsqueda
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-green-800 dark:text-green-200 mb-1">Buscar</label>
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Folio, nombre o correo..."
                    icon="magnifying-glass"
                    class="w-full"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-green-800 dark:text-green-200 mb-1">Estado</label>
                <select wire:model.live="estado_filtro"
                           class="w-full px-3 py-2 border border-green-300 dark:border-green-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500">
                    <option value="">Todos</option>
                    <option value="confirmada">Confirmada</option>
                    <option value="completada">Completada</option>
                </select>
            </div>

            <div class="flex items-end">
                <button wire:click="limpiarFiltros"
                        class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors text-sm">
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto bg-white dark:bg-zinc-900 rounded-lg border-2 border-green-200 dark:border-green-800 shadow-lg">
        <table class="min-w-full divide-y divide-green-200 dark:divide-green-800">
            <thead class="bg-green-800 dark:bg-green-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Folio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Habitación</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Check-in</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Check-out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Servicios</th>
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
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            Hab. {{ $reserva->no_habitacion ?? 'N/A' }}
                        </span>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $reserva->nom_completo }}
                            </span>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400 break-all">
                                {{ $reserva->correo }}
                            </span>
                        </div>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                        {{ \Carbon\Carbon::parse($reserva->fecha_check_in)->format('d/m/Y') }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                        {{ \Carbon\Carbon::parse($reserva->fecha_check_out)->format('d/m/Y') }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $estadoClasses = match($reserva->estado) {
                                'confirmada' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                'completada' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
                            };
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $estadoClasses }}">
                            {{ ucfirst($reserva->estado) }}
                        </span>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ $reserva->total_servicios }} servicio{{ $reserva->total_servicios != 1 ? 's' : '' }}
                        </span>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <button
                            wire:click="abrirModalServicios({{ $reserva->idreservas }})"
                            class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Gestionar
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                        <svg class="mx-auto h-12 w-12 text-zinc-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-sm">No hay reservas para gestionar servicios adicionales</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $reservas->links() }}
    </div>

    @if($mostrarModalServicios)
    <div class="fixed inset-0 z-50 overflow-y-auto" wire:key="modal-servicios" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">

            <div class="fixed inset-0 bg-gray-900 bg-opacity-75" wire:click="cerrarModal"></div>

            <div wire:ignore.self class="inline-block bg-white dark:bg-zinc-900 rounded-lg shadow-xl sm:max-w-2xl w-full relative z-50">

                <div class="bg-green-700 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white flex items-center gap-2">
                        Servicios Adicionales
                    </h3>
                    <button wire:click="cerrarModal" class="text-white hover:text-gray-200">
                        ✖
                    </button>
                </div>

                <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">

                    <div class="mb-6 bg-green-50 dark:bg-green-900/10 p-4 rounded-lg border">
                        <h4 class="font-semibold mb-2">Agregar Nuevo Servicio</h4>

                        <textarea wire:model.live="nuevo_servicio" rows="2"
                            class="w-full border rounded p-2"></textarea>

                        @error('nuevo_servicio')
                            <p class="text-red-500 text-xs">{{ $message }}</p>
                        @enderror

                        <small class="text-gray-500">
                            {{ mb_strlen($nuevo_servicio ?? '') }}/500 caracteres
                        </small>

                        <button wire:click="agregarServicio" class="mt-3 bg-green-600 text-white px-4 py-2 rounded">
                            Guardar
                        </button>
                    </div>

                    @foreach($servicios_reserva as $servicio)
                    <div wire:key="servicio-{{ $servicio->id }}"
                        class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border mb-2">
                        <p>{{ $servicio->descripcion }}</p>
                    </div>
                    @endforeach
                </div>

                <div class="bg-gray-100 dark:bg-gray-800 px-6 py-4">
                    <button wire:click="cerrarModal"
                        class="w-full bg-gray-600 text-white py-2 rounded">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif


</div>
