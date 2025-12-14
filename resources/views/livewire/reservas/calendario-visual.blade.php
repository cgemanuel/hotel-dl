{{-- resources/views/livewire/reservas/calendario-visual.blade.php --}}
<div class="p-6 lg:p-8 bg-white dark:bg-zinc-900">
    <div class="mb-6 border-b-4 border-teal-700 pb-4">
        <flux:heading size="xl" class="mb-2 text-teal-900 dark:text-teal-100">
            Calendario de Reservas
        </flux:heading>
        <flux:subheading class="text-gray-600 dark:text-gray-400">
            Visualiza todas las reservas en formato calendario
        </flux:subheading>
    </div>

    <!-- Controles del Calendario -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-3">
            <button wire:click="mesAnterior"
                    class="p-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white min-w-[200px] text-center">
                @php
                    $meses = [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                    ];
                @endphp
                {{ $meses[$mesActual] }} {{ $anioActual }}
            </h2>

            <button wire:click="mesSiguiente"
                    class="p-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <button wire:click="irHoy"
                class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-medium transition-colors">
            Ir a Hoy
        </button>
    </div>

    <!-- Leyenda -->
    <div class="mb-4 flex flex-wrap gap-4 justify-center bg-teal-50 dark:bg-teal-900/10 p-3 rounded-lg">
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-gray-200 dark:bg-gray-700"></div>
            <span class="text-sm text-gray-700 dark:text-gray-300">Sin reservas</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-green-100 dark:bg-green-900"></div>
            <span class="text-sm text-gray-700 dark:text-gray-300">1-2 reservas</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-yellow-100 dark:bg-yellow-900"></div>
            <span class="text-sm text-gray-700 dark:text-gray-300">3-5 reservas</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-red-100 dark:bg-red-900"></div>
            <span class="text-sm text-gray-700 dark:text-gray-300">6+ reservas</span>
        </div>
    </div>

    <!-- Calendario -->
    <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-xl border border-gray-300 dark:border-gray-700">

        <!-- Encabezado de días -->
        <div class="grid grid-cols-7 bg-teal-800 dark:bg-teal-900 text-white text-center font-semibold text-sm">
            @foreach(['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'] as $dia)
                <div class="py-3 border-r border-teal-700 last:border-r-0">
                    {{ $dia }}
                </div>
            @endforeach
        </div>

        <!-- Cuerpo del calendario -->
        <div class="grid grid-cols-7 auto-rows-[140px]">

            @php
                // Carbon → Domingo = 0 ... Sábado = 6 (correcto)
                $diaSemanaInicio = $primerDia->dayOfWeek;

                $diasEnMes = $ultimoDia->day;
                $diaActual = 1;
                $hoy = now()->format('Y-m-d');
            @endphp

            @for($i = 0; $i < 42; $i++)
                @php
                    $mostrarDia = $i >= $diaSemanaInicio && $diaActual <= $diasEnMes;
                @endphp

                @if($mostrarDia)
                    @php
                        $fechaCompleta = sprintf('%04d-%02d-%02d', $anioActual, $mesActual, $diaActual);
                        $esHoy = $fechaCompleta === $hoy;
                        $cantidadReservas = $reservasPorDia[$diaActual] ?? 0;

                        if ($cantidadReservas == 0) $colorFondo = 'bg-gray-50 dark:bg-gray-800/50';
                        elseif ($cantidadReservas <= 2) $colorFondo = 'bg-green-50 dark:bg-green-900/20';
                        elseif ($cantidadReservas <= 5) $colorFondo = 'bg-yellow-50 dark:bg-yellow-900/20';
                        else $colorFondo = 'bg-red-50 dark:bg-red-900/20';
                    @endphp

                    <button
                        wire:click="verReservasDelDia('{{ $fechaCompleta }}')"
                        class="p-3 border border-gray-200 dark:border-gray-700 text-left relative {{ $colorFondo }} hover:bg-teal-100 dark:hover:bg-teal-900/30 transition-colors"
                    >
                        <span class="text-lg font-bold {{ $esHoy ? 'text-teal-600 dark:text-teal-400' : 'text-gray-900 dark:text-gray-100' }}">
                            {{ $diaActual }}
                        </span>

                        @if($esHoy)
                            <span class="absolute top-2 right-2 px-2 py-0.5 bg-teal-600 text-white text-xs rounded-full">
                                Hoy
                            </span>
                        @endif

                        @if($cantidadReservas > 0)
                            <div class="mt-2 text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd"
                                        d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                        clip-rule="evenodd"/>
                                </svg>
                                {{ $cantidadReservas }} reserva{{ $cantidadReservas > 1 ? 's' : '' }}
                            </div>
                        @endif
                    </button>

                    @php $diaActual++; @endphp

                @else
                    <div class="border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900/40"></div>
                @endif

            @endfor
        </div>
    </div>

    <!-- Modal -->
    @if($mostrarModalDia)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75" wire:click="cerrarModal"></div>

            <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-4xl w-full p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-700 pb-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Reservas del {{ \Carbon\Carbon::parse($fechaSeleccionada)->format('d/m/Y') }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Total: {{ count($reservasDelDia) }} reserva{{ count($reservasDelDia) != 1 ? 's' : '' }}
                        </p>
                    </div>
                    <button wire:click="cerrarModal" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                @if(count($reservasDelDia) > 0)
                    <div class="space-y-4">
                        @foreach($reservasDelDia as $reserva)
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border-l-4
                                {{ $reserva->estado === 'confirmada' ? 'border-green-500' : 'border-yellow-500' }}">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-lg font-bold text-teal-600 dark:text-teal-400">
                                                {{ $reserva->folio }}
                                            </span>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                {{ $reserva->estado === 'confirmada'
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                    : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                                {{ ucfirst($reserva->estado) }}
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <p class="text-gray-600 dark:text-gray-400">Cliente</p>
                                                <p class="font-semibold text-gray-900 dark:text-white">
                                                    {{ $reserva->nom_completo }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600 dark:text-gray-400">Habitación</p>
                                                <p class="font-semibold text-gray-900 dark:text-white">
                                                    No. {{ $reserva->no_habitacion }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600 dark:text-gray-400">Check-in</p>
                                                <p class="font-semibold text-gray-900 dark:text-white">
                                                    {{ \Carbon\Carbon::parse($reserva->fecha_check_in)->format('d/m/Y') }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600 dark:text-gray-400">Check-out</p>
                                                <p class="font-semibold text-gray-900 dark:text-white">
                                                    {{ \Carbon\Carbon::parse($reserva->fecha_check_out)->format('d/m/Y') }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600 dark:text-gray-400">Personas</p>
                                                <p class="font-semibold text-gray-900 dark:text-white">
                                                    {{ $reserva->no_personas }}
                                                </p>
                                            </div>
                                            @if($reserva->telefono)
                                            <div>
                                                <p class="text-gray-600 dark:text-gray-400">Teléfono</p>
                                                <p class="font-semibold text-gray-900 dark:text-white">
                                                    {{ $reserva->telefono }}
                                                </p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <a href="{{ route('reservas.index') }}"
                                       class="ml-4 px-3 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm font-medium transition-colors">
                                        Ver Detalles
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">No hay reservas para este día</p>
                    </div>
                @endif

                <div class="mt-6 flex justify-end border-t border-gray-200 dark:border-gray-700 pt-4">
                    <button wire:click="cerrarModal"
                            class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
