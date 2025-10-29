<div class="p-6 lg:p-8">
    <flux:heading size="xl" class="mb-2">Gestión de Reservas</flux:heading>
    <flux:subheading class="mb-6">Administra las reservas de hotel desde este panel.</flux:subheading>

    <!-- ALERTAS -->
    <!-- Éxito -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <!-- Error -->
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Barra de acciones -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <!-- Campo de busqueda -->
        <flux:input
            wire:model.live="search"
            placeholder="Buscar por cliente o ID..."
            icon="magnifying-glass"
            class="w-full md:w-96"
        />
        <!-- Boton de nueva reserva -->
        <flux:button variant="primary" icon="plus" wire:click="$dispatch('abrirModal')">
            Nueva Reserva
        </flux:button>
    </div>

    <!-- Modal de crear reserva -->
    @livewire('reservas.crear-reserva')

    @include('livewire.reservas.modal-ver', [
        'mostrarModalVer' => $mostrarModalVer,
        'reservaSeleccionada' => $reservaSeleccionada
    ])

    <!-- Modal de editar reserva -->
    @include('livewire.reservas.modal-editar', [
    'mostrarModalEditar' => $mostrarModalEditar
    ])

    <!-- Tabla de reservas -->
    <div class="overflow-x-auto bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Check-in</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Check-out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Personas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Plataforma</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Acciones</th>
                </tr>

                <style>
                    tbody tr:hover {
                        background-color: rgb(244 244 245) !important;
                    }

                    .dark tbody tr:hover {
                        background-color: rgb(63 63 70) !important;
                    }
                </style>

            </thead>

            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                <!-- Ciclo que recorre todas las reservas -->
                @forelse($reservas as $reserva)
                <tr class="cursor-pointer">
                    <!-- ID de la reserva -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                        {{ $reserva->idreservas }}
                    </td>
                    <!-- Datos del cliente -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $reserva->nom_completo }}</span>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $reserva->telefono }}</span>
                        </div>
                    </td>
                    <!-- Fecha de check-in -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                        {{ \Carbon\Carbon::parse($reserva->fecha_check_in)->format('d/m/Y') }}
                    </td>
                    <!-- Fecha de check-out -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                        {{ \Carbon\Carbon::parse($reserva->fecha_check_out)->format('d/m/Y') }}
                    </td>
                    <!-- Personas -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                        {{ $reserva->no_personas }}
                    </td>
                    <!-- Estado -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $estadoClasses = match($reserva->estado) {
                                'confirmada' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                'cancelada' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
                            };
                        @endphp

                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $estadoClasses }}">
                            {{ ucfirst($reserva->estado) }}
                        </span>
                    </td>
                    <!-- Plataforma -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                        {{ $reserva->nombre_plataforma }}
                    </td>
                    <!-- Total con desglose -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                        <div class="text-right">
                            <div class="font-bold text-lg text-green-600 dark:text-green-400">
                                ${{ number_format($reserva->total_calculado['total'], 2) }}
                            </div>

                        </div>
                    </td>
                    <!-- Acciones -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex gap-2">
                            <button wire:click="ver({{ $reserva->idreservas }})"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                Ver
                            </button>
                            <button type="button" wire:click.stop="editar({{ $reserva->idreservas }})"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                Editar
                            </button>
                            <button wire:click="eliminar({{ $reserva->idreservas }})"
                                    wire:confirm="¿Estás seguro de cancelar esta reserva?"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                Cancelar
                            </button>
                        </div>
                    </td>
                </tr>
                <!-- Mensaje mostrado cuando no hay reservas -->
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                        <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-2 text-sm">No hay reservas registradas</p>
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
