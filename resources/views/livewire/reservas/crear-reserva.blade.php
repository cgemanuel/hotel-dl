<div>
    @if($mostrarModal)
    <!-- Modal con z-index específico -->
    <div class="modal-overlay fixed inset-0 overflow-y-auto" style="z-index: 9998;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

            <!-- Overlay oscuro -->
            <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75 dark:bg-black dark:bg-opacity-90"
                 wire:click="cerrar" style="z-index: 9998;"></div>

            <!-- Contenedor del modal -->
            <div class="modal-container inline-block w-full max-w-4xl overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-zinc-900 rounded-lg shadow-2xl sm:my-8 sm:align-middle relative" style="z-index: 9999;">

                <form wire:submit.prevent="guardar">
                    <!-- Header fijo -->
                    <div class="sticky top-0 z-10 px-6 py-4 border-b border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Nueva Reserva
                            </h3>
                            <button wire:click="cerrar"
                                    type="button"
                                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Body con scroll -->
                    <div class="px-6 py-4 max-h-[calc(100vh-200px)] overflow-y-auto bg-white dark:bg-zinc-900">

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

                        <!-- Sección: Cliente -->
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 border-b-2 border-green-600 dark:border-green-500 pb-2">Información del Cliente</h4>

                            <!-- Cliente Existente -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Cliente Existente (opcional)
                                </label>
                                <select wire:model.live="cliente_id" wire:change="seleccionarCliente"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent">
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
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           @if($cliente_existente) readonly @endif
                                           placeholder="País">
                                    @error('pais_origen') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Correo -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Correo Electrónico *
                                    </label>
                                    <input type="email"
                                           wire:model="correo"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           @if($cliente_existente) readonly @endif
                                           placeholder="correo@ejemplo.com">
                                    @error('correo') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-6 border-gray-200 dark:border-zinc-700">

                        <!-- Sección: Reserva -->
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 border-b-2 border-green-600 dark:border-green-500 pb-2">Detalles de la Reserva</h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                <!-- Folio Manual -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Folio de Reserva *
                                    </label>
                                    <input type="text"
                                           wire:model="folio"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           placeholder="Ej: RES-20250105-0001">
                                    @error('folio') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Fecha Check-in -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Fecha Check-in *
                                    </label>
                                    <input type="date"
                                           wire:model="fecha_check_in"
                                           min="{{ date('Y-m-d') }}"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent">
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
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent">
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
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           placeholder="1">
                                    @error('no_personas') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Habitación -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Habitación *
                                    </label>
                                    <select wire:model="habitacion_id"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        <option value="">Seleccionar</option>
                                        @foreach($habitaciones as $habitacion)
                                            <option value="{{ $habitacion->idhabitacion }}">
                                                Hab. {{ $habitacion->no_habitacion }} - {{ $habitacion->tipo }} - ${{ $habitacion->precio }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('habitacion_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Plataforma -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Plataforma de Reserva *
                                    </label>
                                    <select wire:model="plataforma_id"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        <option value="">Seleccionar</option>
                                        @foreach($plataformas as $plataforma)
                                            <option value="{{ $plataforma->idplat_reserva }}">
                                                {{ $plataforma->nombre_plataforma }} ({{ $plataforma->comision }}%)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('plataforma_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Método de Pago -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Método de Pago *
                                    </label>
                                    <select wire:model.live="metodo_pago"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        <option value="">Seleccionar...</option>
                                        <option value="efectivo">Efectivo</option>
                                        <option value="tarjeta">Tarjeta</option>
                                        <option value="transferencia">Transferencia</option>
                                        <option value="combinado">Combinado (2 o más métodos)</option>
                                    </select>
                                    @error('metodo_pago') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer fijo DENTRO DEL FORM -->
                    <div class="sticky bottom-0 px-6 py-4 bg-white dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-700">
                        <div class="flex justify-end gap-3">
                            <button type="button"
                                    wire:click="cerrar"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-600 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                Cancelar
                            </button>

                            <!-- BOTÓN SUBMIT -->
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">

                                <!-- Spinner animado -->
                                <svg wire:loading wire:target="guardar" class="w-5 h-5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
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