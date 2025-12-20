<div class="p-6 lg:p-8 bg-white dark:bg-zinc-900">
    <div class="mb-6 border-b-4 border-purple-700 pb-4">
        <flux:heading size="xl" class="mb-2 text-purple-900 dark:text-purple-100">Gestión de Habitaciones (Gerente)</flux:heading>
        <flux:subheading class="text-gray-600 dark:text-gray-400">Administra las propiedades de las habitaciones del hotel.</flux:subheading>
    </div>

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

    <!-- Filtros y Botón Crear -->
    <div class="mb-6 flex justify-between items-center gap-4">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar habitación..."
            icon="magnifying-glass"
            class="flex-1 max-w-md"
        />

        <flux:button variant="primary" icon="plus" wire:click="abrirModalCrear" class="bg-purple-600 hover:bg-purple-700">
            Nueva Habitación
        </flux:button>
    </div>

    <!-- Tabla de Habitaciones -->
    <div class="overflow-x-auto bg-white dark:bg-zinc-900 rounded-lg border-2 border-purple-200 dark:border-purple-800 shadow-lg">
        <table class="min-w-full divide-y divide-purple-200 dark:divide-purple-800">
            <thead class="bg-purple-800 dark:bg-purple-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">No. Habitación</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Planta</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Acciones</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($habitaciones as $habitacion)
                <tr class="hover:bg-purple-50 dark:hover:bg-purple-900/10">
                    <td class="px-6 py-4 whitespace-nowrap font-bold text-purple-600 dark:text-purple-400">
                        {{ $habitacion->no_habitacion }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap capitalize">{{ $habitacion->tipo }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $habitacion->estado === 'disponible' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $habitacion->estado === 'ocupada' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $habitacion->estado === 'en_mantenimiento' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                            {{ str_replace('_', ' ', ucfirst($habitacion->estado)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $habitacion->planta }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button wire:click="abrirModalEditar({{ $habitacion->idhabitacion }})"
                                class="text-blue-600 hover:text-blue-900 mr-3">
                            Editar
                        </button>
                        <button wire:click="eliminar({{ $habitacion->idhabitacion }})"
                                wire:confirm="¿Estás seguro de eliminar esta habitación?"
                                class="text-red-600 hover:text-red-900">
                            Eliminar
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-zinc-500">
                        No hay habitaciones registradas
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $habitaciones->links() }}
    </div>

    <!-- Modal Crear/Editar -->
    @if($mostrarModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" wire:click.self="cerrarModal">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75"></div>

            <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-md w-full p-6">
                <h3 class="text-xl font-bold mb-4 text-purple-900 dark:text-purple-100">
                    {{ $editando_id ? 'Editar Habitación' : 'Nueva Habitación' }}
                </h3>

                <form wire:submit.prevent="guardar" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">No. Habitación *</label>
                        <input type="number" wire:model="no_habitacion"
                               class="w-full px-3 py-2 border rounded-lg dark:bg-zinc-800 dark:border-zinc-600">
                        @error('no_habitacion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Tipo *</label>
                        <select wire:model="tipo" class="w-full px-3 py-2 border rounded-lg dark:bg-zinc-800 dark:border-zinc-600">
                            <option value="">Seleccionar...</option>
                            <option value="individual">Individual</option>
                            <option value="doble">Doble</option>
                            <option value="suite">Suite</option>
                        </select>
                        @error('tipo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Estado *</label>
                        <select wire:model="estado" class="w-full px-3 py-2 border rounded-lg dark:bg-zinc-800 dark:border-zinc-600">
                            <option value="">Seleccionar...</option>
                            <option value="disponible">Disponible</option>
                            <option value="ocupada">Ocupada</option>
                            <option value="en_mantenimiento">En Mantenimiento</option>
                        </select>
                        @error('estado') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Planta *</label>
                        <select wire:model="planta" class="w-full px-3 py-2 border rounded-lg dark:bg-zinc-800 dark:border-zinc-600">
                            <option value="">Seleccionar...</option>
                            <option value="Planta 1">Planta 1</option>
                            <option value="Planta 2">Planta 2</option>
                            <option value="Planta 3">Planta 3</option>
                        </select>
                        @error('planta') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="cerrarModal"
                                class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            {{ $editando_id ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
