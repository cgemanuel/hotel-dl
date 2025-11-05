<div>
    @if($mostrarModal)
    <!-- Modal con z-index específico para Flux UI -->
    <div class="modal-overlay fixed inset-0 overflow-y-auto" style="z-index: 9998;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay oscuro -->
            <div class="fixed inset-0 transition-opacity bg-green-900 bg-opacity-75 dark:bg-green-900 dark:bg-opacity-90"
                 wire:click="cerrar" style="z-index: 9998;"></div>

            <!-- Contenedor del modal -->
            <div class="modal-container inline-block w-full max-w-4xl overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-zinc-800 rounded-lg shadow-2xl sm:my-8 sm:align-middle relative" style="z-index: 9999;">

                <!-- Header fijo -->
                <div class="sticky top-0 z-10 px-6 py-4 border-b border-green-200 dark:border-zinc-700 bg-green-800">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-white">
                            Nueva Reserva - Folio: {{ $folio }}
                        </h3>
                        <button wire:click="cerrar"
                                type="button"
                                class="text-gray-200 hover:text-white focus:outline-none transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body con scroll -->
                <div class="px-6 py-4 max-h-[calc(100vh-200px)] overflow-y-auto bg-white dark:bg-zinc-800">
                    <!-- Mostrar errores -->
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form wire:submit.prevent="guardar">

                        <!-- Sección: Cliente -->
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-green-900 dark:text-green-100 mb-4 border-b-2 border-green-600 pb-2">Información del Cliente</h4>

                            <!-- Cliente Existente -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Cliente Existente (opcional)
                                </label>
                                <select wire:model.live="cliente_id" wire:change="seleccionarCliente"
                                        class="w-full px-3 py-2 border border-green-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">-- Nuevo Cliente --</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->idclientes }}">{{ $cliente->nom_completo }} - {{ $cliente->correo }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Nombre Completo -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Nombre Completo *
                                    </label>
                                    <input type="text"
                                           wire:model="nom_completo"
                                           class="w-full px-3 py-2 border border-green-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           @if($cliente_existente) readonly @endif
                                           placeholder="Ingrese el nombre completo">
                                    @error('nom_completo') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Tipo de Identificación -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Tipo de Identificación *
                                    </label>
                                    <select wire:model="tipo_identificacion"
                                            class="w-full px-3 py-2 border border-green-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            @if($cliente_existente) disabled @endif>
                                        <option value="">Seleccionar</option>
                                        <option value="INE">INE</option>
                                        <option value="Pasaporte">Pasaporte</option>
                                        <option value="Licencia">Licencia</option>
                                    </select>
                                    @error('tipo_identificacion') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- No. Identificación -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        No. Identificación *
                                    </label>
                                    <input type="text"
                                           wire:model="no_identificacion"
                                           class="w-full px-3 py-2 border border-green-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           @if($cliente_existente) readonly @endif
                                           placeholder="Número de identificación">
                                    @error('no_identificacion') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Edad -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Edad *
                                    </label>
                                    <input type="number"
                                           wire:model="edad"
                                           class="w-full px-3 py-2 border border-green-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           @if($cliente_existente) readonly @endif
                                           placeholder="Edad">
                                    @error('edad') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Dirección -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Dirección *
                                    </label>
                                    <input type="text"
                                           wire:model="direccion"
                                           class="w-full px-3 py-2 border border-green-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           @if($cliente_existente) readonly @endif
                                           placeholder="Dirección completa">
                                    @error('direccion') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Estado de Origen -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Estado *
                                    </label>
                                    <input type="text"
                                           wire:model="estado_origen"
                                           class="w-full px-3 py-2 border border-green-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           @if($cliente_existente) readonly @endif
                                           placeholder="Estado">
                                    @error('estado_origen') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- País -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        País *
                                    </label>
                                    <input type="text"
                                           wire:model="pais_origen"
                                           class="w-full px-3 py-2 border border-green-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           @if($cliente_existente) readonly @endif
                                           placeholder="País">
                                    @error('pais_origen') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Correo (reemplaza teléfono) -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Correo Electrónico *
                                    </label>
                                    <input type="email"
                                           wire:model="correo"
                                           class="w-full px-3 py-2 border border-green-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           @if($cliente_existente) readonly @endif
                                           placeholder="correo@ejemplo.com">
                                    @error('correo') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-6 border-green-200 dark:border-zinc-700">

                        <!-- Sección: Reserva -->
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-green-900 dark:text-green-100 mb-4 border-b-2 border-green-600 pb-2">Detalles de la Reserva</h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Fecha Check-in -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Fecha Check-in *
                                    </label>
                                    <input type="date"
                                           wire:model="fecha_check_in"
                                           min="{{ date('Y-m-d') }}"
                                           class="w-full px-3 py-2 border border-green-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    @error('fecha_check_in') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Fecha Check-out -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Fecha Check-out *
                                    </label>
                                    <input type="date"
                                           wire:model="fecha_check_out"
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                           class="w-full px-3 py-2 border border-green-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    @error('fecha_check_out') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- No. Personas -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        No. Personas *
                                    </label>
                                    <input type="number"
                                           wire:model="no_personas"
                                           min="1"
                                           class="w-full px-3 py-2 border border-green-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           placeholder="1">
                                    @error('no_personas') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Habitación -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Habitación *
                                    </label>
                                    <select wire:model="habitacion_id"
                                            class="w-full px-3 py-2 border border-green-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        <option value="">Seleccionar</option>
                                        @foreach($habitaciones as $habitacion)
                                            <option value="{{ $habitacion->idhabitacion }}">
                                                Hab. {{ $habitacion->no_habitacion }} - {{ $habitacion->tipo }} - ${{ $habitacion->precio }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('habitacion_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- ¿Necesita Estacionamiento? -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        ¿Necesita Estacionamiento?
                                    </label>
                                    <div class="flex gap-6">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="radio"
                                                   wire:model.live="necesita_estacionamiento"
                                                   value="0"
                                                   class="w-4 h-4 text-green-600 focus:ring-green-500 focus:ring-2">
                                            <span class="ml-2 text-sm text-gray-900 dark:text-gray-100">No</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer">
                                            <input type="radio"
                                                   wire:model.live="necesita_estacionamiento"
                                                   value="1"
                                                   class="w-4 h-4 text-green-600 focus:ring-green-500 focus:ring-2">
                                            <span class="ml-2 text-sm text-gray-900 dark:text-gray-100">Sí</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Estacionamiento -->
                                @if($necesita_estacionamiento)
                                <div class="md:col-span-2 transition-all duration-300 ease-in-out">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Seleccionar Espacio de Estacionamiento *
                                    </label>
                                    <select wire:model="espacio_estacionamiento"
                                            class="w-full px-3 py-2 border border-green-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        <option value="">Seleccionar espacio...</option>
                                        @forelse($espacios_estacionamiento as $espacio)
                                            <option value="{{ $espacio->no_espacio }}">
                                                Espacio {{ $espacio->no_espacio }} - {{ ucfirst($espacio->estado) }}
                                            </option>
                                        @empty
                                            <option value="" disabled>No hay espacios disponibles</option>
                                        @endforelse
                                    </select>
                                    @error('espacio_estacionamiento')
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                @endif

                                <!-- Plataforma -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Plataforma de Reserva *
                                    </label>
                                    <select wire:model="plataforma_id"
                                            class="w-full px-3 py-2 border border-green-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        <option value="">Seleccionar</option>
                                        @foreach($plataformas as $plataforma)
                                            <option value="{{ $plataforma->idplat_reserva }}">
                                                {{ $plataforma->nombre_plataforma }} ({{ $plataforma->comision }}%)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('plataforma_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-6 border-green-200 dark:border-zinc-700">

                        <!-- Sección: Método de Pago -->
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-green-900 dark:text-green-100 mb-4 border-b-2 border-amber-600 pb-2">Método de Pago</h4>

                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Seleccione Método de Pago *
                                    </label>
                                    <select wire:model.live="metodo_pago"
                                            class="w-full px-3 py-2 border border-green-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                        <option value="">Seleccionar...</option>
                                        <option value="efectivo">Efectivo</option>
                                        <option value="tarjeta">Tarjeta</option>
                                        <option value="transferencia">Transferencia</option>
                                        <option value="combinado">Combinado (2 o más métodos)</option>
                                    </select>
                                    @error('metodo_pago') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Campos para pago combinado -->
                                @if($metodo_pago === 'combinado')
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border-2 border-amber-400">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Monto Efectivo
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                                            <input type="number"
                                                   wire:model="monto_efectivo"
                                                   step="0.01"
                                                   min="0"
                                                   class="w-full pl-7 pr-3 py-2 border border-amber-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                                   placeholder="0.00">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Monto Tarjeta
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                                            <input type="number"
                                                   wire:model="monto_tarjeta"
                                                   step="0.01"
                                                   min="0"
                                                   class="w-full pl-7 pr-3 py-2 border border-amber-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                                   placeholder="0.00">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Monto Transferencia
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                                            <input type="number"
                                                   wire:model="monto_transferencia"
                                                   step="0.01"
                                                   min="0"
                                                   class="w-full pl-7 pr-3 py-2 border border-amber-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                                   placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="md:col-span-3">
                                        <p class="text-sm text-amber-700 dark:text-amber-300 font-medium">
                                            Total: ${{ number_format($monto_efectivo + $monto_tarjeta + $monto_transferencia, 2) }}
                                        </p>
                                    </div>
                                </div>
                                @endif

                                @if($metodo_pago && $metodo_pago !== 'combinado')
                                <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-300">
                                    <p class="text-sm text-green-700 dark:text-green-300">
                                        ✓ Pago seleccionado: <strong class="capitalize">{{ $metodo_pago }}</strong>
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>

                    </form>
                </div>

                <!-- Footer fijo -->
                <div class="sticky bottom-0 px-6 py-4 bg-green-50 dark:bg-zinc-900 border-t border-green-200 dark:border-zinc-700">
                    <div class="flex justify-end gap-3">
                        <button type="button"
                                wire:click="cerrar"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-600 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            Cancelar
                        </button>
                        <button type="button"
                                wire:click="guardar"
                                class="px-4 py-2 text-sm font-medium text-white bg-amber-600 border border-transparent rounded-lg hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                            Guardar Reserva
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endif
</div>
