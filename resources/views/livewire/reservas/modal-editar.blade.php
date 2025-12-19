<!-- Modal Editar Reserva - ACTUALIZADO CON TOTAL Y VEHÍCULO -->
@if($mostrarModalEditar)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true"
     x-data="{ open: @json($mostrarModalEditar) }"
     @keydown.escape="@this.call('cerrarModalEditar')">

    <!-- Overlay -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity"
        wire:click="cerrarModalEditar"></div>

    <!-- Modal Panel -->
    <div class="relative inline-block align-bottom bg-white dark:bg-zinc-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full mx-4">
        <form wire:submit.prevent="actualizarReserva" @submit.prevent>
            <div class="bg-white dark:bg-zinc-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6 border-b border-zinc-200 dark:border-zinc-700 pb-4">
                    <h3 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                        Editar Reserva #{{ $editando_id }}
                    </h3>
                    <button type="button"
                            @click="@this.call('cerrarModalEditar')"
                            class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Forms en Grid de 2 Columnas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Columna Izquierda -->
                    <div class="space-y-4">
                        <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 border-b border-zinc-200 dark:border-zinc-700 pb-2">
                            Fechas y Personas
                        </h4>

                        <!-- Fecha Reserva -->
                        <div>
                            <label for="edit_fecha_reserva" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Fecha de Reserva *
                            </label>
                            <input type="date" id="edit_fecha_reserva" wire:model="edit_fecha_reserva"
                                   class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('edit_fecha_reserva') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Fecha Check-in -->
                        <div>
                            <label for="edit_fecha_check_in" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Fecha Check-in *
                            </label>
                            <input type="date" id="edit_fecha_check_in" wire:model="edit_fecha_check_in"
                                   class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('edit_fecha_check_in') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Fecha Check-out -->
                        <div>
                            <label for="edit_fecha_check_out" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Fecha Check-out *
                            </label>
                            <input type="date" id="edit_fecha_check_out" wire:model="edit_fecha_check_out"
                                   class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('edit_fecha_check_out') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Número de Personas -->
                        <div>
                            <label for="edit_no_personas" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Número de Personas *
                            </label>
                            <input type="number" id="edit_no_personas" wire:model="edit_no_personas" min="1"
                                   class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('edit_no_personas') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Total Reserva -->
                        <div>
                            <label for="edit_total_reserva" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Total de la Reserva *
                            </label>
                            <input type="number" id="edit_total_reserva" wire:model="edit_total_reserva" min="0" step="0.01"
                                   class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="0.00">
                            @error('edit_total_reserva') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Columna Derecha -->
                    <div class="space-y-4">
                        <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 border-b border-zinc-200 dark:border-zinc-700 pb-2">
                            Estado y Servicios
                        </h4>

                        <!-- Estado -->
                        <div>
                            <label for="edit_estado" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Estado *
                            </label>
                            <select id="edit_estado" wire:model="edit_estado"
                                    class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Seleccionar...</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="confirmada">Confirmada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                            @error('edit_estado') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Cliente (solo lectura) -->
                        <div>
                            <label for="edit_cliente_nombre" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Cliente
                            </label>
                            <input type="text"
                                id="edit_cliente_nombre"
                                value="{{ $reservaSeleccionada?->nom_completo ?? 'No disponible' }}"
                                readonly
                                class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 cursor-not-allowed">
                        </div>

                        <!-- Estacionamiento con Select -->
                        <div>
                            <label for="edit_estacionamiento_no_espacio" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Espacio de Estacionamiento
                            </label>
                            <select id="edit_estacionamiento_no_espacio"
                                    wire:model="edit_estacionamiento_no_espacio"
                                    class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Sin estacionamiento</option>
                                @foreach($espacios_disponibles ?? [] as $espacio)
                                    <option value="{{ $espacio->no_espacio }}">
                                        Espacio {{ $espacio->no_espacio }}
                                        @if($espacio->estado === 'disponible')
                                            - Disponible
                                        @else
                                            - Asignado actualmente
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('edit_estacionamiento_no_espacio') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tipo de Vehículo -->
                        <div>
                            <label for="edit_tipo_vehiculo" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Tipo de Vehículo
                            </label>
                            <input type="text" id="edit_tipo_vehiculo" wire:model="edit_tipo_vehiculo"
                                   class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Ej: Sedán, SUV, Camioneta">
                            @error('edit_tipo_vehiculo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Descripción del Vehículo -->
                        <div>
                            <label for="edit_descripcion_vehiculo" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Descripción del Vehículo
                            </label>
                            <textarea id="edit_descripcion_vehiculo" wire:model="edit_descripcion_vehiculo" rows="3"
                                      class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Ej: Toyota Corolla 2020, color gris, placas ABC-123"></textarea>
                            @error('edit_descripcion_vehiculo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-zinc-50 dark:bg-zinc-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                <button type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:w-auto sm:text-sm">
                    Guardar Cambios
                </button>
                <button type="button"
                        @click="@this.call('cerrarModalEditar')"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
@endif
