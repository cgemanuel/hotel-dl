@if($mostrarModalEditar)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true"
     x-data="{
         open: @json($mostrarModalEditar),
         seccionFechas: true,
         seccionEstado: true
     }"
     @keydown.escape="@this.call('cerrarModalEditar')">

    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity"
         wire:click="cerrarModalEditar"></div>

    <div class="relative inline-block align-bottom bg-white dark:bg-zinc-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full mx-4">
        <form wire:submit.prevent="actualizarReserva" @submit.prevent>
            <div class="bg-white dark:bg-zinc-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div class="border border-zinc-300 dark:border-zinc-700 rounded-lg overflow-hidden">
                        <button type="button"
                                @click="seccionFechas = !seccionFechas"
                                class="w-full flex items-center justify-between px-4 py-3 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                            <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100">
                                Fechas y Personas
                            </h4>
                            <svg class="h-5 w-5 text-zinc-600 dark:text-zinc-400 transition-transform duration-200"
                                 :class="{ 'rotate-180': seccionFechas }"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="seccionFechas"
                             x-collapse
                             class="px-4 py-4 space-y-4 bg-white dark:bg-zinc-900">

                            <div>
                                <label for="edit_fecha_reserva" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                    Fecha de Reserva *
                                </label>
                                <input type="date" id="edit_fecha_reserva" wire:model="edit_fecha_reserva"
                                       class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('edit_fecha_reserva') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="edit_fecha_check_in" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                    Fecha Check-in *
                                </label>
                                <input type="date" id="edit_fecha_check_in" wire:model="edit_fecha_check_in"
                                       class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('edit_fecha_check_in') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="edit_fecha_check_out" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                    Fecha Check-out *
                                </label>
                                <input type="date" id="edit_fecha_check_out" wire:model="edit_fecha_check_out"
                                       class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('edit_fecha_check_out') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="edit_no_personas" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                    Número de Personas *
                                </label>
                                <input type="number" id="edit_no_personas" wire:model="edit_no_personas" min="1"
                                       class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('edit_no_personas') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

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
                    </div>

                    <div class="border border-zinc-300 dark:border-zinc-700 rounded-lg overflow-hidden">
                        <button type="button"
                                @click="seccionEstado = !seccionEstado"
                                class="w-full flex items-center justify-between px-4 py-3 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                            <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100">
                                Estado y Servicios
                            </h4>
                            <svg class="h-5 w-5 text-zinc-600 dark:text-zinc-400 transition-transform duration-200"
                                 :class="{ 'rotate-180': seccionEstado }"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="seccionEstado"
                             x-collapse
                             class="px-4 py-4 space-y-4 bg-white dark:bg-zinc-900">

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

                            {{-- ── Cliente: Nombre ── --}}
                            <div>
                                <label for="edit_nom_completo" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                    Cliente
                                    <span class="ml-1 text-xs font-normal text-blue-500 dark:text-blue-400">(puedes corregir el nombre)</span>
                                </label>
                                <input type="text"
                                       id="edit_nom_completo"
                                       wire:model="edit_nom_completo"
                                       placeholder="Nombre completo del cliente"
                                       class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('edit_nom_completo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- ── Cliente: Correo (NUEVO CAMPO) ── --}}
                            <div>
                                <label for="edit_correo" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                    Correo Electrónico
                                    <span class="ml-1 text-xs font-normal text-blue-500 dark:text-blue-400">(contacto)</span>
                                </label>
                                <input type="email"
                                       id="edit_correo"
                                       wire:model="edit_correo"
                                       placeholder="ejemplo@correo.com"
                                       class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('edit_correo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- ── Método de Pago ── --}}
                            <div>
                                <label for="edit_metodo_pago" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                    Método de Pago
                                    <span class="ml-1 text-xs font-normal text-blue-500 dark:text-blue-400">(actualiza si aparece "Tarjeta")</span>
                                </label>
                                <select id="edit_metodo_pago"
                                        wire:model.live="edit_metodo_pago"
                                        class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Seleccionar...</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta_debito">Tarjeta de Débito</option>
                                    <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="combinado">Combinado</option>
                                    @if($edit_metodo_pago === 'tarjeta')
                                        <option value="tarjeta">Tarjeta (valor antiguo)</option>
                                    @endif
                                </select>
                                @error('edit_metodo_pago') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                                @if($edit_metodo_pago === 'tarjeta')
                                <p class="mt-1 flex items-start gap-1 text-xs text-amber-600 dark:text-amber-400">
                                    <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Esta reserva usa el formato antiguo. Selecciona <strong class="mx-0.5">Tarjeta de Débito</strong> o <strong class="mx-0.5">Tarjeta de Crédito</strong> para actualizarla.
                                </p>
                                @endif
                            </div>

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
                                            @if($espacio->estado === 'disponible') - Disponible @else - Asignado actualmente @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('edit_estacionamiento_no_espacio') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="edit_tipo_vehiculo" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                    Tipo de Vehículo
                                </label>
                                <input type="text" id="edit_tipo_vehiculo" wire:model="edit_tipo_vehiculo"
                                       class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Ej: Sedán, SUV, Camioneta">
                                @error('edit_tipo_vehiculo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

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
            </div>

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
