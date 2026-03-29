<div>
    @if($mostrarModal)
    <div class="modal-overlay fixed inset-0 overflow-y-auto" style="z-index: 9998;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75 dark:bg-black dark:bg-opacity-90"
                 wire:click="cerrar" style="z-index: 9998;"></div>

            <div class="modal-container inline-block w-full max-w-4xl overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-zinc-900 rounded-lg shadow-2xl sm:my-8 sm:align-middle relative" style="z-index: 9999;">
                <form wire:submit.prevent="guardar">

                    {{-- Header --}}
                    <div class="sticky top-0 z-10 px-6 py-4 border-b border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Nueva Reserva</h3>
                            <button wire:click="cerrar" type="button" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 max-h-[calc(100vh-200px)] overflow-y-auto bg-white dark:bg-zinc-900">

                        @if ($errors->any())
                            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400">
                                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- ── CLIENTE ── --}}
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 border-b-2 border-green-600 dark:border-green-500 pb-2">
                                Información del Cliente
                            </h4>

                            {{-- Nombre con AUTOCOMPLETE --}}
                            <div class="mb-4 relative" x-data="{ open: @entangle('mostrar_sugerencias') }">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Nombre Completo *
                                        @if($cliente_existente)
                                            <span class="ml-2 px-2 py-0.5 text-xs bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 rounded-full font-semibold">
                                                ✓ Cliente registrado
                                            </span>
                                        @endif
                                    </label>
                                    @if($cliente_existente)
                                        <button type="button" wire:click="limpiarClienteSeleccionado"
                                                class="text-xs text-red-500 hover:text-red-700 underline">
                                            Nuevo cliente
                                        </button>
                                    @endif
                                </div>

                                <input type="text"
                                       wire:model.live.debounce.300ms="nom_completo"
                                       @if($cliente_existente) readonly @endif
                                       placeholder="Escribe el nombre para buscar o crear nuevo..."
                                       autocomplete="off"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 @if($cliente_existente) opacity-70 cursor-not-allowed @endif">

                                {{-- Sugerencias de clientes existentes --}}
                                @if($mostrar_sugerencias && !$cliente_existente && count($sugerencias_clientes) > 0)
                                    <div class="absolute z-50 w-full mt-1 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-600 rounded-lg shadow-xl max-h-48 overflow-y-auto">
                                        <div class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-zinc-700 border-b border-gray-200 dark:border-zinc-600 font-medium">
                                            Clientes registrados — haz clic para seleccionar
                                        </div>
                                        @foreach($sugerencias_clientes as $sug)
                                            <button type="button"
                                                    wire:click="seleccionarClienteAutocomplete({{ $sug->idclientes }})"
                                                    class="w-full text-left px-4 py-3 hover:bg-green-50 dark:hover:bg-green-900/20 border-b border-gray-100 dark:border-zinc-700 last:border-0 transition-colors">
                                                <span class="block text-sm font-semibold text-gray-900 dark:text-white">{{ $sug->nom_completo }}</span>
                                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $sug->tipo_identificacion }} · {{ $sug->pais_origen }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif

                                @error('nom_completo') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                {{-- Tipo de identificación --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Tipo de Identificación *
                                    </label>
                                    <select wire:model="tipo_identificacion"
                                            @if($cliente_existente) disabled @endif
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 @if($cliente_existente) opacity-70 @endif">
                                        <option value="">Seleccionar</option>
                                        <option value="INE">INE</option>
                                        <option value="Pasaporte">Pasaporte</option>
                                        <option value="Licencia">Licencia</option>
                                    </select>
                                    @error('tipo_identificacion') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- Dirección --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Dirección *
                                    </label>
                                    <input type="text" wire:model="direccion"
                                           @if($cliente_existente) readonly @endif
                                           placeholder="Calle, número, colonia"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 @if($cliente_existente) opacity-70 @endif">
                                    @error('direccion') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- País --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        País *
                                    </label>
                                    <input type="text" wire:model="pais_origen"
                                           @if($cliente_existente) readonly @endif
                                           placeholder="Ej: México"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 @if($cliente_existente) opacity-70 @endif">
                                    @error('pais_origen') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                            </div>
                        </div>

                        <hr class="my-6 border-gray-200 dark:border-zinc-700">

                        {{-- ── DETALLES DE RESERVA ── --}}
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 border-b-2 border-green-600 dark:border-green-500 pb-2">
                                Detalles de la Reserva
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                {{-- Folio --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Folio de Reserva *</label>
                                    <input type="text" wire:model="folio" placeholder="Ej: RES-20250105-0001"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                    @error('folio') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- Check-in --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Check-in *</label>
                                    <input type="date" wire:model.live="fecha_check_in"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                    @error('fecha_check_in') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- Check-out --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Check-out *</label>
                                    <input type="date" wire:model.live="fecha_check_out"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                    @error('fecha_check_out') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- No. personas --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">No. Personas *</label>
                                    <input type="number" wire:model="no_personas" min="1"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                    @error('no_personas') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- Plataforma --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Plataforma de Reserva *</label>
                                    <select wire:model="plataforma_id"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500">
                                        <option value="">Seleccionar</option>
                                        @foreach($plataformas as $plataforma)
                                            <option value="{{ $plataforma->idplat_reserva }}">
                                                {{ $plataforma->nombre_plataforma }} ({{ $plataforma->comision }}%)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('plataforma_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- ── HABITACIONES ── --}}
                                <div class="md:col-span-2"
                                     x-data="{
                                         sel: @js($habitaciones_ids),
                                         toggle(id) {
                                             const n = parseInt(id);
                                             const idx = this.sel.indexOf(n);
                                             if (idx === -1) { this.sel.push(n); } else { this.sel.splice(idx, 1); }
                                             $wire.set('habitaciones_ids', this.sel, false);
                                         },
                                         has(id) { return this.sel.includes(parseInt(id)); }
                                     }"
                                     x-on:livewire:navigated.window="sel = @js($habitaciones_ids)"
                                >
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Habitaciones *
                                        <span class="ml-1 text-xs font-normal text-gray-500 dark:text-gray-400">— puedes seleccionar una o varias</span>
                                    </label>

                                    @if($fecha_check_in && $fecha_check_out)
                                        <div class="mb-2 flex items-center gap-1.5 text-xs text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg px-3 py-2">
                                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Disponibles del
                                            <strong class="ml-1">{{ \Carbon\Carbon::parse($fecha_check_in)->format('d/m/Y') }}</strong>
                                            al
                                            <strong class="ml-1">{{ \Carbon\Carbon::parse($fecha_check_out)->format('d/m/Y') }}</strong>
                                        </div>
                                    @endif

                                    @error('habitaciones_ids')
                                        <p class="mb-2 text-red-500 text-xs">{{ $message }}</p>
                                    @enderror

                                    @if($habitaciones->count() > 0)
                                        <div class="mb-2">
                                            <span class="text-xs font-semibold px-2 py-1 rounded-full"
                                                  :class="sel.length > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500'"
                                                  x-text="sel.length === 0 ? 'Ninguna seleccionada' : sel.length + (sel.length === 1 ? ' habitación seleccionada' : ' habitaciones seleccionadas')">
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 p-3 rounded-lg border-2 transition-colors duration-200"
                                             :class="sel.length > 0 ? 'border-green-400 bg-green-50 dark:bg-green-900/10' : 'border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800'">

                                            @foreach($habitaciones as $hab)
                                            <button type="button"
                                                    @click="toggle({{ $hab->idhabitacion }})"
                                                    class="flex items-center gap-2 p-2 rounded-lg transition-all duration-150 text-left w-full focus:outline-none"
                                                    :class="has({{ $hab->idhabitacion }})
                                                        ? 'bg-green-600 text-white shadow ring-2 ring-green-400'
                                                        : 'bg-white dark:bg-zinc-900 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-zinc-700 hover:border-green-400 hover:bg-green-50 dark:hover:bg-green-900/20'"
                                            >
                                                <span class="flex-shrink-0 w-5 h-5 rounded flex items-center justify-center"
                                                      :class="has({{ $hab->idhabitacion }}) ? 'bg-white/30' : 'bg-gray-100 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-600'">
                                                    <svg x-show="has({{ $hab->idhabitacion }})"
                                                         class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </span>
                                                <span class="min-w-0">
                                                    <span class="block text-sm font-bold leading-tight">Hab. {{ $hab->no_habitacion }}</span>
                                                    <span class="block text-xs leading-tight capitalize"
                                                          :class="has({{ $hab->idhabitacion }}) ? 'text-green-100' : 'text-gray-500 dark:text-gray-400'">
                                                        {{ $hab->tipo }}
                                                    </span>
                                                </span>
                                            </button>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="p-4 text-center text-sm bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-lg">
                                            <p class="font-semibold text-red-700 dark:text-red-400">No hay habitaciones disponibles</p>
                                        </div>
                                    @endif
                                </div>
                                {{-- /HABITACIONES --}}

                                {{-- ── MÉTODO DE PAGO ── --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Método de Pago *</label>
                                    <select wire:model.live="metodo_pago"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500">
                                        <option value="">Seleccionar...</option>
                                        <option value="efectivo">Efectivo</option>
                                        <option value="tarjeta_debito">Tarjeta de Débito</option>
                                        <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                        <option value="transferencia">Transferencia</option>
                                        <option value="combinado">Combinado</option>
                                        <option value="cortesia">Cortesía</option>
                                    </select>
                                    @error('metodo_pago') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- ── DESGLOSE COMBINADO ── --}}
                                @if($metodo_pago === 'combinado')
                                <div class="md:col-span-2 bg-amber-50 dark:bg-amber-900/10 border-2 border-amber-300 dark:border-amber-700 rounded-xl p-4">
                                    <h5 class="text-sm font-bold text-amber-900 dark:text-amber-100 mb-3 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                        </svg>
                                        Desglose del Pago Combinado
                                        <span class="text-xs font-normal text-amber-700 dark:text-amber-300">(llena los que apliquen)</span>
                                    </h5>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">💵 Monto Efectivo</label>
                                            <div class="flex items-center gap-1">
                                                <span class="text-gray-500 text-sm">$</span>
                                                <input type="number" step="0.01" min="0" wire:model.live="monto_efectivo" placeholder="0.00"
                                                       class="flex-1 px-2 py-1.5 text-sm border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">💳 Monto T. Débito</label>
                                            <div class="flex items-center gap-1">
                                                <span class="text-gray-500 text-sm">$</span>
                                                <input type="number" step="0.01" min="0" wire:model.live="monto_tarjeta_debito" placeholder="0.00"
                                                       class="flex-1 px-2 py-1.5 text-sm border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">💳 Monto T. Crédito</label>
                                            <div class="flex items-center gap-1">
                                                <span class="text-gray-500 text-sm">$</span>
                                                <input type="number" step="0.01" min="0" wire:model.live="monto_tarjeta_credito" placeholder="0.00"
                                                       class="flex-1 px-2 py-1.5 text-sm border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">🏦 Monto Transferencia</label>
                                            <div class="flex items-center gap-1">
                                                <span class="text-gray-500 text-sm">$</span>
                                                <input type="number" step="0.01" min="0" wire:model.live="monto_transferencia" placeholder="0.00"
                                                       class="flex-1 px-2 py-1.5 text-sm border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100">
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Suma en tiempo real --}}
                                    @php
                                        $sumaCombinado = floatval($monto_efectivo) + floatval($monto_tarjeta_debito) + floatval($monto_tarjeta_credito) + floatval($monto_transferencia);
                                    @endphp
                                    <div class="mt-3 pt-3 border-t border-amber-300 dark:border-amber-600 flex justify-between items-center">
                                        <span class="text-xs text-amber-800 dark:text-amber-200 font-medium">Suma de montos:</span>
                                        <span class="text-base font-bold text-amber-800 dark:text-amber-200">${{ number_format($sumaCombinado, 2) }}</span>
                                    </div>
                                </div>
                                @endif

                                {{-- Aviso cortesía --}}
                                @if($metodo_pago === 'cortesia')
                                    <div class="md:col-span-2 flex items-center gap-2 px-3 py-2 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg text-xs text-purple-700 dark:text-purple-300">
                                        Esta reserva se registrará como cortesía — sin cargo al huésped.
                                    </div>
                                @endif

                                {{-- Total --}}
                                <div class="md:col-span-2 bg-white dark:bg-zinc-800 p-4 rounded-lg border-2 border-gray-300 dark:border-zinc-600">
                                    <label class="block text-sm font-medium text-amber-900 dark:text-amber-100 mb-2">Total de la Reserva *</label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl font-bold text-amber-600 dark:text-amber-400">$</span>
                                        <input type="number" step="0.01" min="0" wire:model="total_reserva" placeholder="0.00"
                                               class="flex-1 px-4 py-3 text-xl font-bold border-2 border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500">
                                    </div>
                                    @error('total_reserva') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="sticky bottom-0 px-6 py-4 bg-white dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-700">
                        <div class="flex justify-end gap-3">
                            <button type="button" wire:click="cerrar"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-600 rounded-lg hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-60">
                                <svg wire:loading wire:target="guardar" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="guardar">Guardar Reserva</span>
                                <span wire:loading wire:target="guardar">Guardando...</span>
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
    @endif
</div>
