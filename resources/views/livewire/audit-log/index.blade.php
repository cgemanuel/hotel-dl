{{-- resources/views/livewire/audit-log/index.blade.php --}}
<div class="p-6 lg:p-8 bg-white dark:bg-zinc-900">
    <div class="mb-6 border-b-4 border-purple-700 pb-4">
        <flux:heading size="xl" class="mb-2 text-purple-900 dark:text-purple-100">
            Historial de Auditoría
        </flux:heading>
        <flux:subheading class="text-gray-600 dark:text-gray-400">
            Registro completo de acciones realizadas en el sistema
        </flux:subheading>
    </div>

    <!-- Filtros -->
    <div class="mb-6 bg-purple-50 dark:bg-purple-900/10 p-4 rounded-lg border-2 border-purple-200 dark:border-purple-800">
        <h3 class="text-lg font-semibold text-purple-900 dark:text-purple-100 mb-4">
            Filtros de Búsqueda
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Búsqueda General -->
            <div>
                <label class="block text-sm font-medium text-purple-800 dark:text-purple-200 mb-1">
                    Búsqueda General
                </label>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Usuario, modelo, ID..."
                       class="w-full px-3 py-2 border border-purple-300 dark:border-purple-700 rounded-lg bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-purple-500">
            </div>

            <!-- Acción -->
            <div>
                <label class="block text-sm font-medium text-purple-800 dark:text-purple-200 mb-1">
                    Acción
                </label>
                <select wire:model.live="action_filter"
                        class="w-full px-3 py-2 border border-purple-300 dark:border-purple-700 rounded-lg bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-purple-500">
                    <option value="">Todas</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}">{{ ucfirst($action) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Modelo -->
            <div>
                <label class="block text-sm font-medium text-purple-800 dark:text-purple-200 mb-1">
                    Modelo
                </label>
                <select wire:model.live="model_filter"
                        class="w-full px-3 py-2 border border-purple-300 dark:border-purple-700 rounded-lg bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-purple-500">
                    <option value="">Todos</option>
                    @foreach($models as $model)
                        <option value="{{ $model }}">{{ $model }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Usuario -->
            <div>
                <label class="block text-sm font-medium text-purple-800 dark:text-purple-200 mb-1">
                    Usuario
                </label>
                <select wire:model.live="user_filter"
                        class="w-full px-3 py-2 border border-purple-300 dark:border-purple-700 rounded-lg bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-purple-500">
                    <option value="">Todos</option>
                    @foreach($usuarios as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Fecha Inicio -->
            <div>
                <label class="block text-sm font-medium text-purple-800 dark:text-purple-200 mb-1">
                    Fecha Desde
                </label>
                <input type="date"
                       wire:model.live="fecha_inicio"
                       class="w-full px-3 py-2 border border-purple-300 dark:border-purple-700 rounded-lg bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-purple-500">
            </div>

            <!-- Fecha Fin -->
            <div>
                <label class="block text-sm font-medium text-purple-800 dark:text-purple-200 mb-1">
                    Fecha Hasta
                </label>
                <input type="date"
                       wire:model.live="fecha_fin"
                       class="w-full px-3 py-2 border border-purple-300 dark:border-purple-700 rounded-lg bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-purple-500">
            </div>
        </div>

        <div class="mt-4">
            <button wire:click="limpiarFiltros"
                    class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors text-sm">
                Limpiar Filtros
            </button>
        </div>
    </div>

    <!-- Tabla de Logs -->
    <div class="overflow-x-auto bg-white dark:bg-zinc-900 rounded-lg border-2 border-purple-200 dark:border-purple-800 shadow-lg">
        <table class="min-w-full divide-y divide-purple-200 dark:divide-purple-800">
            <thead class="bg-purple-800 dark:bg-purple-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Fecha/Hora</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Usuario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Acción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Modelo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">IP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Acciones</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($logs as $log)
                <tr class="hover:bg-purple-50 dark:hover:bg-purple-900/10">
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 bg-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-xs">
                                {{ substr($log->user_name, 0, 2) }}
                            </div>
                            <div class="ml-2 text-sm text-gray-900 dark:text-white">
                                {{ $log->user_name }}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $actionColors = [
                                'created' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                'updated' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                'deleted' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                            ];
                            $colorClass = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full {{ $colorClass }}">
                            {{ ucfirst($log->action) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                        {{ $log->model }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                        #{{ $log->model_id ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                        {{ $log->ip_address }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button wire:click="verDetalles({{ $log->id }})"
                                class="text-purple-600 hover:text-purple-900 dark:text-purple-400 font-medium">
                            Ver Detalles
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-zinc-500">
                        No hay registros de auditoría
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $logs->links() }}
    </div>

    <!-- Modal de Detalles -->
    @if($mostrarDetalles && $logSeleccionado)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75" wire:click="cerrarDetalles"></div>

            <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-2xl w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                        Detalles del Registro
                    </h3>
                    <button wire:click="cerrarDetalles" class="text-gray-500 hover:text-gray-700">
                        ✕
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-gray-500">Usuario</label>
                            <p class="font-semibold">{{ $logSeleccionado->user_name }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Fecha/Hora</label>
                            <p class="font-semibold">{{ $logSeleccionado->created_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Acción</label>
                            <p class="font-semibold">{{ ucfirst($logSeleccionado->action) }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Modelo</label>
                            <p class="font-semibold">{{ $logSeleccionado->model }} #{{ $logSeleccionado->model_id }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">IP</label>
                            <p class="font-semibold">{{ $logSeleccionado->ip_address }}</p>
                        </div>
                    </div>

                    @if($logSeleccionado->changes)
                    <div class="border-t pt-4">
                        <h4 class="font-semibold mb-2">Cambios Realizados:</h4>
                        <div class="space-y-2">
                            @foreach($logSeleccionado->changes as $field => $change)
                            <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $field }}</p>
                                <div class="flex gap-4 mt-1">
                                    <div class="flex-1">
                                        <span class="text-xs text-gray-500">Antes:</span>
                                        <p class="text-sm text-red-600">{{ $change['old'] ?? 'N/A' }}</p>
                                    </div>
                                    <div class="flex-1">
                                        <span class="text-xs text-gray-500">Después:</span>
                                        <p class="text-sm text-green-600">{{ $change['new'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end">
                    <button wire:click="cerrarDetalles"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
