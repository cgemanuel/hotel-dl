<div class="p-6 lg:p-8 bg-white dark:bg-zinc-900">
    <div class="mb-6 border-b-4 border-purple-700 pb-4">
        <flux:heading size="xl" class="mb-2 text-purple-900 dark:text-purple-100">Gestión de Estacionamiento (Gerente)</flux:heading>
        <flux:subheading class="text-gray-600 dark:text-gray-400">Administra los espacios de estacionamiento del hotel.</flux:subheading>
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
    @endif>

    <div class="mb-6">
        <flux:button variant="primary" icon="plus" wire:click="abrirModal" class="bg-purple-600 hover:bg-purple-700">
            Nuevo Espacio
        </flux:button>
    </div>

    <!-- Grid de Espacios -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @foreach($espacios as $espacio)
            <div class="relative border-2 rounded-lg p-4 text-center
                {{ $espacio->estado === 'disponible' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-red-500 bg-red-50 dark:bg-red-900/20' }}">

                <button wire:click="eliminar({{ $espacio->no_espacio }})"
                        wire:confirm="¿Eliminar el espacio {{ $espacio->no_espacio }}?"
                        class="absolute top-1 right-1 text-red-600 hover:text-red-900">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <div class="text-2xl font-bold text-purple-700 dark:text-purple-400">
                    {{ $espacio->no_espacio }}
                </div>
                <div class="text-xs mt-1 capitalize font-semibold
                    {{ $espacio->estado === 'disponible' ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                    {{ $espacio->estado }}
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Crear Espacio -->
    @if($mostrarModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" wire:click.self="cerrarModal">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75"></div>

            <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-md w-full p-6">
                <h3 class="text-xl font-bold mb-4 text-purple-900 dark:text-purple-100">
                    Nuevo Espacio de Estacionamiento
                </h3>

                <form wire:submit.prevent="guardar">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Número de Espacio *</label>
                        <input type="number" wire:model="no_espacio"
                               class="w-full px-3 py-2 border rounded-lg dark:bg-zinc-800 dark:border-zinc-600"
                               placeholder="Ej: 10">
                        @error('no_espacio') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="cerrarModal"
                                class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            Crear
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
