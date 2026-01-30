<div class="p-6 lg:p-8 bg-white dark:bg-zinc-900">
    <div class="mb-6 border-b-4 border-red-700 pb-4">
        <flux:heading size="xl" class="mb-2 text-red-900 dark:text-red-100">
            Panel de Superusuario
        </flux:heading>
        <flux:subheading class="text-gray-600 dark:text-gray-400">
            Gestión completa de usuarios del sistema
        </flux:subheading>
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

    @if (session()->has('password_reset'))
        <div class="mb-4 p-4 bg-blue-100 dark:bg-blue-900/20 border-l-4 border-blue-600 text-blue-800 dark:text-blue-200 rounded-lg">
            <div class="flex items-center justify-between">
                <span>{{ session('password_reset') }}</span>
                <button onclick="navigator.clipboard.writeText('{{ substr(session('password_reset'), strpos(session('password_reset'), ': ') + 2) }}')"
                        class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                    Copiar
                </button>
            </div>
        </div>
    @endif

    <!-- Filtros -->
    <div class="mb-6 bg-red-50 dark:bg-zinc-800 p-4 rounded-lg border-2 border-red-200 dark:border-red-700">
        <h3 class="text-lg font-semibold text-red-900 dark:text-red-100 mb-4">Filtros</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Buscar</label>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Nombre, email, teléfono..."
                       class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Rol</label>
                <select wire:model.live="rol_filtro" class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100">
                    <option value="">Todos</option>
                    <option value="recepcionista">Recepcionista</option>
                    <option value="gerente">Gerente</option>
                    <option value="superusuario">Superusuario</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button wire:click="limpiarFiltros" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    Limpiar
                </button>
                <button wire:click="abrirModalCrear" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Nuevo Usuario
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto bg-white dark:bg-zinc-800 rounded-lg border-2 border-red-200 dark:border-red-700 shadow-lg">
        <table class="min-w-full divide-y divide-red-200 dark:divide-red-800">
            <thead class="bg-red-800 dark:bg-red-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Teléfono</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Rol</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($usuarios as $usuario)
                <tr class="hover:bg-red-50 dark:hover:bg-zinc-700">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-100">{{ $usuario->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <div class="h-8 w-8 rounded-full bg-red-600 flex items-center justify-center text-white font-semibold">
                                {{ substr($usuario->name, 0, 1) }}
                            </div>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $usuario->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $usuario->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $usuario->telefono }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $badgeColors = [
                                'recepcionista' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                'gerente' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                'superusuario' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                            ];
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badgeColors[$usuario->rol] }}">
                            {{ ucfirst($usuario->rol) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex gap-2">
                            <button wire:click="abrirModalEditar({{ $usuario->id }})"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                Editar
                            </button>
                            <button wire:click="resetPassword({{ $usuario->id }})"
                                    wire:confirm="¿Resetear contraseña de {{ $usuario->name }}?"
                                    class="text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 font-medium">
                                Reset Pass
                            </button>
                            @if($usuario->id !== auth()->id())
                            <button wire:click="eliminar({{ $usuario->id }})"
                                    wire:confirm="¿Eliminar a {{ $usuario->name }}? Esta acción es irreversible."
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium">
                                Eliminar
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                        No hay usuarios registrados
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $usuarios->links() }}
    </div>

    <!-- Modal Crear/Editar -->
    @if($mostrarModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75" wire:click="cerrarModal"></div>

            <div class="relative bg-white dark:bg-zinc-800 rounded-lg max-w-md w-full p-6">
                <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">
                    {{ $editando_id ? 'Editar Usuario' : 'Nuevo Usuario' }}
                </h3>

                <form wire:submit.prevent="guardar" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Nombre *</label>
                        <input type="text" wire:model="name"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Email *</label>
                        <input type="email" wire:model="email"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Teléfono *</label>
                        <input type="text" wire:model="telefono"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100">
                        @error('telefono') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Rol *</label>
                        <select wire:model="rol" class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100">
                            <option value="recepcionista">Recepcionista</option>
                            <option value="gerente">Gerente</option>
                            <option value="superusuario">Superusuario</option>
                        </select>
                        @error('rol') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                            Contraseña {{ $editando_id ? '(dejar vacío para no cambiar)' : '*' }}
                        </label>
                        <input type="password" wire:model="password"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100">
                        @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Confirmar Contraseña</label>
                        <input type="password" wire:model="password_confirmation"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100">
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="cerrarModal"
                                class="px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-700 text-gray-700 dark:text-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            {{ $editando_id ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
