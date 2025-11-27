<div class="p-6 lg:p-8 bg-white dark:bg-zinc-900">
    <div class="mb-6 border-b-4 border-purple-700 pb-4">
        <flux:heading size="xl" class="mb-2 text-purple-900 dark:text-purple-100">Reportes de Ingresos</flux:heading>
        <flux:subheading class="text-gray-600 dark:text-gray-400">
            Genera reportes concentrados de ingresos por método de pago o por recepcionista
        </flux:subheading>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-blue-100 dark:bg-blue-900/20 border-l-4 border-blue-600 text-blue-800 dark:text-blue-200 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <!-- Panel de Configuración del Reporte -->
    <div class="mb-6 bg-purple-50 dark:bg-purple-900/10 p-6 rounded-lg border-2 border-purple-200 dark:border-purple-800">
        <h3 class="text-lg font-semibold text-purple-900 dark:text-purple-100 mb-4">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Configuración del Reporte
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Tipo de Reporte -->
            <div>
                <label class="block text-sm font-medium text-purple-800 dark:text-purple-200 mb-2">
                    Tipo de Reporte *
                </label>
                <select wire:model.live="tipo_reporte"
                        class="w-full px-3 py-2 border border-purple-300 dark:border-purple-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500">
                    <option value="metodos_pago">Ingresos por Método de Pago</option>
                    <option value="reservas_usuario">Reservas por Recepcionista</option>
                </select>
            </div>

            <!-- Fecha Inicio -->
            <div>
                <label class="block text-sm font-medium text-purple-800 dark:text-purple-200 mb-2">
                    Fecha Inicio *
                </label>
                <input type="date" wire:model="fecha_inicio"
                       class="w-full px-3 py-2 border border-purple-300 dark:border-purple-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500">
                @error('fecha_inicio') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Fecha Fin -->
            <div>
                <label class="block text-sm font-medium text-purple-800 dark:text-purple-200 mb-2">
                    Fecha Fin *
                </label>
                <input type="date" wire:model="fecha_fin"
                       class="w-full px-3 py-2 border border-purple-300 dark:border-purple-700 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500">
                @error('fecha_fin') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <button wire:click="generarReporte"
                    class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors">
                Generar Reporte
            </button>

            @if($reporte_generado)
            <button wire:click="limpiarReporte"
                    class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors">
                Limpiar
            </button>
            <button wire:click="exportarPDF"
                    class="flex items-center gap-2 px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                </svg>
                Exportar PDF
            </button>
            <button wire:click="exportarExcel"
                    class="flex items-center gap-2 px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
                Exportar Excel
            </button>
            @endif
        </div>
    </div>

    <!-- Reporte: Métodos de Pago -->
    @if($reporte_generado && $tipo_reporte === 'metodos_pago')
    <div class="bg-white dark:bg-zinc-900 rounded-lg border-2 border-purple-200 dark:border-purple-800 shadow-lg overflow-hidden">
        <div class="bg-purple-800 dark:bg-purple-900 px-6 py-4">
            <h3 class="text-xl font-bold text-white">
                Concentrado de Ingresos por Método de Pago
            </h3>
            <p class="text-purple-200 text-sm mt-1">
                Del {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }}
                al {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}
            </p>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Efectivo -->
                <div class="bg-green-50 dark:bg-green-900/20 p-6 rounded-lg border-2 border-green-200 dark:border-green-800">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-semibold text-green-800 dark:text-green-200">EFECTIVO</h4>
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-green-700 dark:text-green-300">
                        ${{ number_format($totales_metodos['efectivo'], 2) }}
                    </p>
                </div>

                <!-- Tarjeta -->
                <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg border-2 border-blue-200 dark:border-blue-800">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-200">TARJETA</h4>
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-blue-700 dark:text-blue-300">
                        ${{ number_format($totales_metodos['tarjeta'], 2) }}
                    </p>
                </div>

                <!-- Transferencia -->
                <div class="bg-purple-50 dark:bg-purple-900/20 p-6 rounded-lg border-2 border-purple-200 dark:border-purple-800">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-semibold text-purple-800 dark:text-purple-200">TRANSFERENCIA</h4>
                        <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 5a1 1 0 100 2h5.586l-1.293 1.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L13.586 5H8zM12 15a1 1 0 100-2H6.414l1.293-1.293a1 1 0 10-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L6.414 15H12z"/>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-purple-700 dark:text-purple-300">
                        ${{ number_format($totales_metodos['transferencia'], 2) }}
                    </p>
                </div>

                <!-- Total General -->
                <div class="bg-amber-50 dark:bg-amber-900/20 p-6 rounded-lg border-2 border-amber-200 dark:border-amber-800">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-semibold text-amber-800 dark:text-amber-200">TOTAL GENERAL</h4>
                        <svg class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-amber-700 dark:text-amber-300">
                        ${{ number_format($totales_metodos['total_general'], 2) }}
                    </p>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    <span class="font-semibold">Total de Reservas:</span> {{ $totales_metodos['cantidad_reservas'] }}
                </p>
                @if($totales_metodos['combinado'] > 0)
                <p class="text-sm text-gray-700 dark:text-gray-300 mt-2">
                    <span class="font-semibold">Nota:</span>
                    Se registraron ${{ number_format($totales_metodos['combinado'], 2) }} en pagos combinados
                    (ya incluidos en los totales por método)
                </p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Reporte: Reservas por Usuario -->
    @if($reporte_generado && $tipo_reporte === 'reservas_usuario')
    <div class="bg-white dark:bg-zinc-900 rounded-lg border-2 border-purple-200 dark:border-purple-800 shadow-lg overflow-hidden">
        <div class="bg-purple-800 dark:bg-purple-900 px-6 py-4">
            <h3 class="text-xl font-bold text-white">
                Reporte de Reservas por Recepcionista
            </h3>
            <p class="text-purple-200 text-sm mt-1">
                Del {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }}
                al {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-purple-200 dark:divide-purple-800">
                <thead class="bg-purple-100 dark:bg-purple-900/30">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                            Recepcionista
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                            Total Reservas
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                            Confirmadas
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                            Completadas
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-purple-900 dark:text-purple-100 uppercase tracking-wider">
                            Canceladas
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($reservas_usuario as $usuario)
                    <tr class="hover:bg-purple-50 dark:hover:bg-purple-900/10">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-semibold">
                                        {{ substr($usuario->usuario, 0, 1) }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $usuario->usuario }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-lg font-bold text-purple-600 dark:text-purple-400">
                                {{ $usuario->total_reservas }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                {{ $usuario->confirmadas }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $usuario->completadas }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                {{ $usuario->canceladas }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                            No hay datos para el período seleccionado
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if(!$reporte_generado)
    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
        <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p>Selecciona las opciones y genera un reporte para visualizar los datos</p>
    </div>
    @endif
</div>
