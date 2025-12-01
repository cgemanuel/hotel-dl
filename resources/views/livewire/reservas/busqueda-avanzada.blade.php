<div class="p-6 lg:p-8 bg-white dark:bg-zinc-900">
    <div class="mb-6 border-b-4 border-indigo-700 pb-4">
        <flux:heading size="xl" class="mb-2 text-indigo-900 dark:text-indigo-100">
            Búsqueda Avanzada de Reservas
        </flux:heading>
        <flux:subheading class="text-gray-600 dark:text-gray-400">
            Filtra y encuentra reservas con criterios específicos
        </flux:subheading>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-blue-100 dark:bg-blue-900/20 border-l-4 border-blue-600 text-blue-800 dark:text-blue-200 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <!-- Panel de Filtros Colapsable -->
    <div class="mb-6 bg-indigo-50 dark:bg-indigo-900/10 rounded-lg border-2 border-indigo-200 dark:border-indigo-800">
        <button
            wire:click="$toggle('mostrarFiltros')"
            class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-indigo-100 dark:hover:bg-indigo-900/20 transition-colors rounded-t-lg"
        >
            <h3 class="text-lg font-semibold text-indigo-900 dark:text-indigo-100 flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filtros de Búsqueda Avanzada
            </h3>
            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 transition-transform {{ $mostrarFiltros ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        @if($mostrarFiltros)
        <div class="p-6 border-t border-indigo-200 dark:border-indigo-800">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <!-- Folio -->
                <div>
                    <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-1">
                        Folio
                    </label>
                    <input type="text"
                           wire:model.live.debounce.300ms="folio"
                           placeholder="Ej: RES-2025..."
                           class="w-full px-3 py-2 border border-indigo-300 dark:border-indigo-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Nombre Cliente -->
                <div>
                    <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-1">
                        Nombre del Cliente
                    </label>
                    <input type="text"
                           wire:model.live.debounce.300ms="nombre_cliente"
                           placeholder="Nombre completo..."
                           class="w-full px-3 py-2 border border-indigo-300 dark:border-indigo-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Correo -->
                <div>
                    <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-1">
                        Correo Electrónico
                    </label>
                    <input type="email"
                           wire:model.live.debounce.300ms="correo"
                           placeholder="correo@ejemplo.com"
                           class="w-full px-3 py-2 border border-indigo-300 dark:border-indigo-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Teléfono -->
                <div>
                    <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-1">
                        Teléfono
                    </label>
                    <input type="text"
                           wire:model.live.debounce.300ms="telefono"
                           placeholder="999123456"
                           class="w-full px-3 py-2 border border-indigo-300 dark:border-indigo-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- No. Habitación -->
                <div>
                    <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-1">
                        No. Habitación
                    </label>
                    <input type="number"
                           wire:model.live.debounce.300ms="no_habitacion"
                           placeholder="101"
                           class="w-full px-3 py-2 border border-indigo-300 dark:border-indigo-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Fecha Inicio -->
                <div>
                    <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-1">
                        Fecha Check-in Desde
                    </label>
                    <input type="date"
                           wire:model.live="fecha_inicio"
                           class="w-full px-3 py-2 border border-indigo-300 dark:border-indigo-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Fecha Fin -->
                <div>
                    <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-1">
                        Fecha Check-in Hasta
                    </label>
                    <input type="date"
                           wire:model.live="fecha_fin"
                           class="w-full px-3 py-2 border border-indigo-300 dark:border-indigo-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Estado -->
                <div>
                    <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-1">
                        Estado
                    </label>
                    <select wire:model.live="estado"
                            class="w-full px-3 py-2 border border-indigo-300 dark:border-indigo-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todos</option>
                        <option value="confirmada">Confirmada</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="cancelada">Cancelada</option>
                        <option value="completada">Completada</option>
                    </select>
                </div>

                <!-- Plataforma -->
                <div>
                    <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-1">
                        Plataforma de Reserva
                    </label>
                    <select wire:model.live="plataforma"
                            class="w-full px-3 py-2 border border-indigo-300 dark:border-indigo-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todas</option>
                        @foreach($plataformas as $plat)
                            <option value="{{ $plat->idplat_reserva }}">{{ $plat->nombre_plataforma }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Método de Pago -->
                <div>
                    <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-1">
                        Método de Pago
                    </label>
                    <select wire:model.live="metodo_pago"
                            class="w-full px-3 py-2 border border-indigo-300 dark:border-indigo-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todos</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="combinado">Combinado</option>
                    </select>
                </div>

                <!-- Tipo de Habitación -->
                <div>
                    <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-1">
                        Tipo de Habitación
                    </label>
                    <select wire:model.live="tipo_habitacion"
                            class="w-full px-3 py-2 border border-indigo-300 dark:border-indigo-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todos</option>
                        <option value="individual">Individual</option>
                        <option value="doble">Doble</option>
                        <option value="suite">Suite</option>
                    </select>
                </div>

                <!-- Estacionamiento -->
                <div>
                    <label class="block text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-1">
                        Con Estacionamiento
                    </label>
                    <select wire:model.live="tiene_estacionamiento"
                            class="w-full px-3 py-2 border border-indigo-300 dark:border-indigo-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todos</option>
                        <option value="si">Sí</option>
                        <option value="no">No</option>
                    </select>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex gap-3 mt-6 pt-4 border-t border-indigo-200 dark:border-indigo-800">
                <button wire:click="limpiarFiltros"
                        class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Limpiar Filtros
                </button>

                <button wire:click="exportarExcel"
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exportar a Excel
                </button>
            </div>
        </div>
        @endif
    </div>

    <!-- Información de resultados -->
    <div class="mb-4 flex justify-between items-center">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Mostrando {{ $reservas->count() }} de {{ $reservas->total() }} resultados
        </p>
    </div>

    <!-- Tabla de Resultados -->
    <div class="overflow-x-auto bg-white dark:bg-zinc-900 rounded-lg border-2 border-indigo-200 dark:border-indigo-800 shadow-lg">
        <table class="min-w-full divide-y divide-indigo-200 dark:divide-indigo-800">
            <thead class="bg-indigo-800 dark:bg-indigo-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Folio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Habitación</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Check-in</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Plataforma</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Estacionamiento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($reservas as $reserva)
                <tr class="hover:bg-indigo-50 dark:hover:bg-indigo-900/10 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-bold text-indigo-700 dark:text-indigo-400">
                            {{ $reserva->folio }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $reserva->nom_completo }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $reserva->correo }}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-medium rounded bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                            {{ $reserva->no_habitacion }} - {{ ucfirst($reserva->tipo_habitacion ?? 'N/A') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        {{ \Carbon\Carbon::parse($reserva->fecha_check_in)->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $estadoClasses = match($reserva->estado) {
                                'confirmada' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                'cancelada' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                'completada' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $estadoClasses }}">
                            {{ ucfirst($reserva->estado) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        {{ $reserva->nombre_plataforma ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @if($reserva->estacionamiento_no_espacio)
                            <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                Esp. {{ $reserva->estacionamiento_no_espacio }}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('reservas.index') }}"
                           class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                            Ver Detalles
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                        <svg class="mx-auto h-12 w-12 text-zinc-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <p>No se encontraron reservas con los criterios de búsqueda</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-6">
        {{ $reservas->links() }}
    </div>
</div>
