@if($mostrarModalEditar)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true"
     x-data="{
         open: @json($mostrarModalEditar),
         seccionFechas: true,
         seccionHabitaciones: false,
         seccionCliente: true,
         seccionEstado: true
     }"
     @keydown.escape="@this.call('cerrarModalEditar')">

    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity"
         wire:click="cerrarModalEditar"></div>

    <div class="relative inline-block align-bottom bg-white dark:bg-zinc-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full mx-4 max-h-[90vh] flex flex-col">
        <form wire:submit.prevent="actualizarReserva" @submit.prevent>

            {{-- Header --}}
            <div class="bg-white dark:bg-zinc-900 px-4 pt-5 pb-4 sm:p-6 border-b border-zinc-200 dark:border-zinc-700 flex-shrink-0">
                <div class="flex items-center justify-between">
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
            </div>

            {{-- Body con scroll --}}
            <div class="flex-grow overflow-y-auto px-4 py-4 sm:px-6 bg-white dark:bg-zinc-900" style="max-height: calc(90vh - 160px);">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- ══ Columna izquierda ══ --}}
                    <div class="space-y-4">

                        {{-- Acordeón: Folio --}}
                        <div class="border border-amber-300 dark:border-amber-600 rounded-lg overflow-hidden">
                            <div class="px-4 py-3 bg-amber-50 dark:bg-amber-900/20">
                                <label class="block text-sm font-semibold text-amber-900 dark:text-amber-100 mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    Folio de Reserva *
                                </label>
                                <input type="text" wire:model="edit_folio"
                                       placeholder="Ej: RES-20250105-0001"
                                       class="w-full px-3 py-2 border border-amber-300 dark:border-amber-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-amber-500 focus:border-transparent font-mono text-sm">
                                @error('edit_folio') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Acordeón: Fechas y Personas --}}
                        <div class="border border-zinc-300 dark:border-zinc-700 rounded-lg overflow-hidden">
                            <button type="button"
                                    @click="seccionFechas = !seccionFechas"
                                    class="w-full flex items-center justify-between px-4 py-3 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100">Fechas y Personas</h4>
                                <svg class="h-5 w-5 text-zinc-600 dark:text-zinc-400 transition-transform duration-200"
                                     :class="{ 'rotate-180': seccionFechas }"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="seccionFechas" x-collapse class="px-4 py-4 space-y-4 bg-white dark:bg-zinc-900">
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Fecha de Reserva *</label>
                                    <input type="date" wire:model="edit_fecha_reserva"
                                           class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('edit_fecha_reserva') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Fecha Check-in *</label>
                                    <input type="date" wire:model="edit_fecha_check_in"
                                           class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('edit_fecha_check_in') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Fecha Check-out *</label>
                                    <input type="date" wire:model="edit_fecha_check_out"
                                           class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('edit_fecha_check_out') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Número de Personas *</label>
                                    <input type="number" wire:model="edit_no_personas" min="1"
                                           class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('edit_no_personas') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Total de la Reserva *</label>
                                    <input type="number" wire:model="edit_total_reserva" min="0" step="0.01" placeholder="0.00"
                                           class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('edit_total_reserva') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Acordeón: Datos del Cliente --}}
                        <div class="border border-blue-300 dark:border-blue-600 rounded-lg overflow-hidden">
                            <button type="button"
                                    @click="seccionCliente = !seccionCliente"
                                    class="w-full flex items-center justify-between px-4 py-3 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-700 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <h4 class="text-md font-semibold text-blue-900 dark:text-blue-100">Datos del Cliente</h4>
                                </div>
                                <svg class="h-5 w-5 text-blue-700 dark:text-blue-300 transition-transform duration-200"
                                     :class="{ 'rotate-180': seccionCliente }"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="seccionCliente" x-collapse class="px-4 py-4 space-y-4 bg-white dark:bg-zinc-900">

                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nombre Completo *</label>
                                    <input type="text" wire:model="edit_nom_completo" placeholder="Nombre completo del cliente"
                                           class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('edit_nom_completo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tipo de Identificación</label>
                                    <select wire:model="edit_tipo_identificacion"
                                            class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Seleccionar...</option>
                                        <option value="INE">INE</option>
                                        <option value="Pasaporte">Pasaporte</option>
                                        <option value="Licencia">Licencia</option>
                                    </select>
                                    @error('edit_tipo_identificacion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Dirección</label>
                                    <input type="text" wire:model="edit_direccion" placeholder="Calle, número, colonia"
                                           class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('edit_direccion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">País de Origen</label>
                                    <input type="text" wire:model="edit_pais_origen" placeholder="Ej: México"
                                           class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('edit_pais_origen') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                            </div>
                        </div>

                        {{-- Acordeón: Habitaciones --}}
                        <div class="border border-green-300 dark:border-green-500 rounded-lg overflow-hidden">
                            <button type="button"
                                    @click="seccionHabitaciones = !seccionHabitaciones"
                                    class="w-full flex items-center justify-between px-4 py-3 bg-green-100 dark:bg-green-800 hover:bg-green-200 dark:hover:bg-green-700 transition-colors">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-700 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    <h4 class="text-md font-semibold text-green-900 dark:text-green-100">
                                        Habitaciones
                                        <span x-data="{ count: @js(count($edit_habitaciones_ids ?? [])) }"
                                              x-text="count > 0 ? '(' + count + ')' : ''"
                                              class="text-xs font-normal"></span>
                                    </h4>
                                </div>
                                <svg class="h-5 w-5 text-green-700 dark:text-green-300 transition-transform duration-200"
                                     :class="{ 'rotate-180': seccionHabitaciones }"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="seccionHabitaciones" x-collapse
                                 class="px-4 py-4 bg-white dark:bg-zinc-900"
                                 x-data="{
                                     sel: @js($edit_habitaciones_ids ?? []),
                                     toggle(id) {
                                         const n = parseInt(id);
                                         const idx = this.sel.indexOf(n);
                                         if (idx === -1) { this.sel.push(n); }
                                         else { this.sel.splice(idx, 1); }
                                         $wire.set('edit_habitaciones_ids', this.sel, false);
                                     },
                                     has(id) { return this.sel.includes(parseInt(id)); }
                                 }">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                    Selecciona habitaciones *
                                    <span class="ml-1 text-xs font-normal text-blue-500 dark:text-blue-400">(agregar o quitar)</span>
                                </label>
                                @error('edit_habitaciones_ids')
                                    <p class="mb-2 text-red-500 text-xs">{{ $message }}</p>
                                @enderror
                                <div class="mb-2">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                          :class="sel.length > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'"
                                          x-text="sel.length === 0 ? '⚠ Ninguna seleccionada' : sel.length + (sel.length === 1 ? ' habitación' : ' habitaciones')">
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-1.5 p-2 rounded-lg border-2 transition-colors"
                                     :class="sel.length > 0 ? 'border-green-400 bg-green-50 dark:bg-green-900/10' : 'border-red-300 bg-red-50 dark:bg-red-900/10'">
                                    @if(isset($edit_habitaciones_disponibles) && count($edit_habitaciones_disponibles) > 0)
                                        @foreach($edit_habitaciones_disponibles as $hab)
                                        <button type="button" @click="toggle({{ $hab->idhabitacion }})"
                                                class="flex items-center gap-1.5 p-2 rounded-lg transition-all duration-150 text-left w-full focus:outline-none text-sm"
                                                :class="has({{ $hab->idhabitacion }})
                                                    ? 'bg-green-600 text-white shadow ring-2 ring-green-400'
                                                    : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 hover:border-green-400 hover:bg-green-50 dark:hover:bg-green-900/20'">
                                            <span class="flex-shrink-0 w-4 h-4 rounded flex items-center justify-center"
                                                  :class="has({{ $hab->idhabitacion }}) ? 'bg-white/30' : 'bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600'">
                                                <svg x-show="has({{ $hab->idhabitacion }})" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </span>
                                            <span>
                                                <span class="block font-bold leading-tight">{{ $hab->no_habitacion }}</span>
                                                <span class="block text-xs leading-tight capitalize"
                                                      :class="has({{ $hab->idhabitacion }}) ? 'text-green-100' : 'text-zinc-400 dark:text-zinc-500'">
                                                    {{ $hab->tipo }}
                                                </span>
                                            </span>
                                        </button>
                                        @endforeach
                                    @else
                                        <p class="col-span-2 text-xs text-zinc-400 text-center py-2">Sin habitaciones disponibles</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- ══ Columna derecha: Estado, Plataforma, Vehículo ══ --}}
                    <div class="border border-zinc-300 dark:border-zinc-700 rounded-lg overflow-hidden">
                        <button type="button"
                                @click="seccionEstado = !seccionEstado"
                                class="w-full flex items-center justify-between px-4 py-3 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                            <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100">Estado y Servicios</h4>
                            <svg class="h-5 w-5 text-zinc-600 dark:text-zinc-400 transition-transform duration-200"
                                 :class="{ 'rotate-180': seccionEstado }"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="seccionEstado" x-collapse class="px-4 py-4 space-y-4 bg-white dark:bg-zinc-900">

                            {{-- Estado --}}
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Estado *</label>
                                <select wire:model="edit_estado"
                                        class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Seleccionar...</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="confirmada">Confirmada</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                                @error('edit_estado') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Plataforma --}}
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Plataforma de Reserva</label>
                                <select wire:model="edit_plataforma_id"
                                        class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Sin plataforma (directa)</option>
                                    @foreach($plataformas ?? [] as $plat)
                                        <option value="{{ $plat->idplat_reserva }}">
                                            {{ $plat->nombre_plataforma }} ({{ $plat->comision }}%)
                                        </option>
                                    @endforeach
                                </select>
                                @error('edit_plataforma_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- ── Método de pago CON CORTESÍA ── --}}
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                    Método de Pago
                                </label>
                                <select wire:model.live="edit_metodo_pago"
                                        class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Seleccionar...</option>
                                    <option value="efectivo"> Efectivo</option>
                                    <option value="tarjeta_debito">Tarjeta de Débito</option>
                                    <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                    <option value="transferencia"> Transferencia</option>
                                    <option value="combinado">Combinado</option>
                                    <option value="cortesia">Cortesía</option>
                                    @if($edit_metodo_pago === 'tarjeta')
                                        <option value="tarjeta">Tarjeta (valor antiguo)</option>
                                    @endif
                                </select>
                                @error('edit_metodo_pago') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- ── Monto individual según método ── --}}
                            @if($edit_metodo_pago === 'efectivo')
                                <div class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-700 rounded-lg p-3">
                                    <label class="block text-xs font-bold text-green-800 dark:text-green-300 mb-1">💵 Monto en Efectivo</label>
                                    <div class="flex items-center gap-1">
                                        <span class="text-gray-500">$</span>
                                        <input type="number" step="0.01" min="0" wire:model="edit_monto_efectivo" placeholder="0.00"
                                            class="flex-1 px-3 py-2 text-sm border border-green-300 dark:border-green-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                    </div>
                                </div>

                            @elseif($edit_metodo_pago === 'tarjeta_debito')
                                <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-700 rounded-lg p-3">
                                    <label class="block text-xs font-bold text-blue-800 dark:text-blue-300 mb-1">💳 Monto Tarjeta de Débito</label>
                                    <div class="flex items-center gap-1">
                                        <span class="text-gray-500">$</span>
                                        <input type="number" step="0.01" min="0" wire:model="edit_monto_tarjeta_debito" placeholder="0.00"
                                            class="flex-1 px-3 py-2 text-sm border border-blue-300 dark:border-blue-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                    </div>
                                </div>

                            @elseif($edit_metodo_pago === 'tarjeta_credito')
                                <div class="bg-indigo-50 dark:bg-indigo-900/10 border border-indigo-200 dark:border-indigo-700 rounded-lg p-3">
                                    <label class="block text-xs font-bold text-indigo-800 dark:text-indigo-300 mb-1">💳 Monto Tarjeta de Crédito</label>
                                    <div class="flex items-center gap-1">
                                        <span class="text-gray-500">$</span>
                                        <input type="number" step="0.01" min="0" wire:model="edit_monto_tarjeta_credito" placeholder="0.00"
                                            class="flex-1 px-3 py-2 text-sm border border-indigo-300 dark:border-indigo-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                    </div>
                                </div>

                            @elseif($edit_metodo_pago === 'transferencia')
                                <div class="bg-purple-50 dark:bg-purple-900/10 border border-purple-200 dark:border-purple-700 rounded-lg p-3">
                                    <label class="block text-xs font-bold text-purple-800 dark:text-purple-300 mb-1">🏦 Monto Transferencia</label>
                                    <div class="flex items-center gap-1">
                                        <span class="text-gray-500">$</span>
                                        <input type="number" step="0.01" min="0" wire:model="edit_monto_transferencia" placeholder="0.00"
                                            class="flex-1 px-3 py-2 text-sm border border-purple-300 dark:border-purple-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                    </div>
                                </div>

                            @elseif($edit_metodo_pago === 'combinado')
                                <div class="bg-amber-50 dark:bg-amber-900/10 border-2 border-amber-300 dark:border-amber-700 rounded-xl p-4">
                                    <h5 class="text-xs font-bold text-amber-900 dark:text-amber-100 mb-3 flex items-center gap-2">
                                        🔀 Desglose del Pago Combinado
                                        <span class="text-xs font-normal text-amber-700 dark:text-amber-300">(llena los que apliquen)</span>
                                    </h5>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">💵 Efectivo</label>
                                            <div class="flex items-center gap-1">
                                                <span class="text-gray-500 text-sm">$</span>
                                                <input type="number" step="0.01" min="0" wire:model.live="edit_monto_efectivo" placeholder="0.00"
                                                    class="flex-1 px-2 py-1.5 text-sm border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">💳 T. Débito</label>
                                            <div class="flex items-center gap-1">
                                                <span class="text-gray-500 text-sm">$</span>
                                                <input type="number" step="0.01" min="0" wire:model.live="edit_monto_tarjeta_debito" placeholder="0.00"
                                                    class="flex-1 px-2 py-1.5 text-sm border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">💳 T. Crédito</label>
                                            <div class="flex items-center gap-1">
                                                <span class="text-gray-500 text-sm">$</span>
                                                <input type="number" step="0.01" min="0" wire:model.live="edit_monto_tarjeta_credito" placeholder="0.00"
                                                    class="flex-1 px-2 py-1.5 text-sm border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">🏦 Transferencia</label>
                                            <div class="flex items-center gap-1">
                                                <span class="text-gray-500 text-sm">$</span>
                                                <input type="number" step="0.01" min="0" wire:model.live="edit_monto_transferencia" placeholder="0.00"
                                                    class="flex-1 px-2 py-1.5 text-sm border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Suma en tiempo real --}}
                                    @php
                                        $sumaEdit = floatval($edit_monto_efectivo ?? 0)
                                            + floatval($edit_monto_tarjeta_debito ?? 0)
                                            + floatval($edit_monto_tarjeta_credito ?? 0)
                                            + floatval($edit_monto_transferencia ?? 0);
                                    @endphp
                                    <div class="mt-3 pt-3 border-t border-amber-300 dark:border-amber-600 flex justify-between items-center">
                                        <span class="text-xs text-amber-800 dark:text-amber-200 font-medium">Suma de montos:</span>
                                        <span class="text-base font-bold text-amber-800 dark:text-amber-200">${{ number_format($sumaEdit, 2) }}</span>
                                    </div>
                                </div>

                            @elseif($edit_metodo_pago === 'cortesia')
                                <div class="flex items-center gap-2 px-3 py-2 bg-pink-50 dark:bg-pink-900/20 border border-pink-200 dark:border-pink-700 rounded-lg">
                                    <span class="text-xs text-pink-700 dark:text-pink-300 font-medium">🎁 Reserva marcada como cortesía — sin cargo al huésped</span>
                                </div>
                            @endif

                            {{-- Estacionamiento --}}
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Espacio de Estacionamiento</label>
                                <select wire:model="edit_estacionamiento_no_espacio"
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

                            {{-- Tipo Vehículo --}}
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tipo de Vehículo</label>
                                <input type="text" wire:model="edit_tipo_vehiculo" placeholder="Ej: Sedán, SUV, Camioneta"
                                       class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('edit_tipo_vehiculo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Descripción Vehículo --}}
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Descripción del Vehículo</label>
                                <textarea wire:model="edit_descripcion_vehiculo" rows="3"
                                          placeholder="Ej: Toyota Corolla 2020, color gris, placas ABC-123"
                                          class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                                @error('edit_descripcion_vehiculo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-zinc-50 dark:bg-zinc-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3 border-t border-zinc-200 dark:border-zinc-700 flex-shrink-0">
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
