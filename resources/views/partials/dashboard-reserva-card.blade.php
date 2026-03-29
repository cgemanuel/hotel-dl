{{--
    Partial: resources/views/partials/dashboard-reserva-card.blade.php
    Variables: $reserva (object), $tipo (string: activa|checkin|checkout)
--}}
@php
    $cardId = 'card-' . $reserva->idreservas . '-' . $tipo;

    $borderColor = match($tipo) {
        'checkin'  => 'border-blue-300 dark:border-blue-700',
        'checkout' => 'border-orange-300 dark:border-orange-700',
        default    => 'border-green-300 dark:border-green-700',
    };
    $headerBg = match($tipo) {
        'checkin'  => 'bg-blue-600',
        'checkout' => 'bg-orange-600',
        default    => 'bg-green-700',
    };

    $noches = \Carbon\Carbon::parse($reserva->fecha_check_in)
        ->diffInDays(\Carbon\Carbon::parse($reserva->fecha_check_out));

    // Servicios adicionales de esta reserva
    $servicios = DB::table('servicios_adicionales')
        ->where('reservas_idreservas', $reserva->idreservas)
        ->orderBy('fecha_registro', 'desc')
        ->get();
@endphp

<div class="mb-3 rounded-xl border-2 {{ $borderColor }} overflow-hidden shadow-sm"
     x-data="{ open: false }" id="{{ $cardId }}">

    {{-- ── CABECERA (siempre visible) ── --}}
    <div class="{{ $headerBg }} px-4 py-3 flex items-center justify-between cursor-pointer"
         @click="open = !open">
        <div class="flex items-center gap-3 flex-wrap">
            {{-- Avatar --}}
            <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                {{ strtoupper(substr($reserva->nom_completo, 0, 2)) }}
            </div>
            {{-- Info básica --}}
            <div>
                <p class="text-white font-bold text-sm leading-tight">{{ $reserva->nom_completo }}</p>
                <p class="text-white/80 text-xs">
                    Folio: <strong>{{ $reserva->folio }}</strong>
                    · Hab. <strong>{{ $reserva->no_habitacion ?? 'N/A' }}</strong>
                    · {{ $noches }} noche{{ $noches != 1 ? 's' : '' }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-shrink-0">
            {{-- Fechas --}}
            <div class="hidden sm:block text-right">
                <p class="text-white/80 text-xs">
                    {{ \Carbon\Carbon::parse($reserva->fecha_check_in)->format('d/m') }}
                    → {{ \Carbon\Carbon::parse($reserva->fecha_check_out)->format('d/m/Y') }}
                </p>
                <p class="text-white/70 text-xs">{{ $reserva->no_personas }} persona{{ $reserva->no_personas != 1 ? 's' : '' }}</p>
            </div>

            {{-- Botón liberar (solo activas y check-outs) --}}
            @if(in_array($tipo, ['activa', 'checkout']))
                <form method="POST" action="{{ route('dashboard.liberar', $reserva->idreservas) }}"
                      onsubmit="return confirm('¿Confirmar check-out y liberar habitación de {{ addslashes($reserva->nom_completo) }}?')">
                    @csrf
                    <button type="submit"
                            class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white text-xs font-bold rounded-lg border border-white/30 transition-colors">
                        Check-out ✓
                    </button>
                </form>
            @endif

            {{-- Chevron --}}
            <svg class="w-5 h-5 text-white transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>

    {{-- ── CUERPO EXPANDIBLE ── --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-white dark:bg-zinc-900 px-4 py-4">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- ── COLUMNA 1: Datos del huésped ── --}}
            <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                <h4 class="text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wide mb-3 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Huésped
                </h4>

                <div class="space-y-2 text-sm">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nombre</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $reserva->nom_completo }}</p>
                    </div>
                    @if($reserva->pais_origen)
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">País de origen</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $reserva->pais_origen }}</p>
                    </div>
                    @endif
                    <div class="pt-2 border-t border-blue-200 dark:border-blue-700 grid grid-cols-2 gap-2">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Check-in</p>
                            <p class="font-bold text-blue-700 dark:text-blue-300 text-xs">
                                {{ \Carbon\Carbon::parse($reserva->fecha_check_in)->format('d/m/Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Check-out</p>
                            <p class="font-bold text-orange-600 dark:text-orange-400 text-xs">
                                {{ \Carbon\Carbon::parse($reserva->fecha_check_out)->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Habitación(es)</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ $reserva->no_habitacion ?? 'N/A' }}
                            @if($reserva->tipo_habitacion)
                                <span class="text-xs text-gray-500 dark:text-gray-400 capitalize">· {{ $reserva->tipo_habitacion }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- ── COLUMNA 2: Servicios adicionales ── --}}
            <div class="bg-purple-50 dark:bg-purple-900/10 border border-purple-200 dark:border-purple-700 rounded-lg p-4">
                <h4 class="text-xs font-bold text-purple-800 dark:text-purple-300 uppercase tracking-wide mb-3 flex items-center gap-1 justify-between">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Servicios Adicionales
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $servicios->count() > 0 ? 'bg-purple-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                        {{ $servicios->count() }}
                    </span>
                </h4>

                @if($servicios->count() > 0)
                    <ul class="space-y-2 max-h-40 overflow-y-auto">
                        @foreach($servicios as $servicio)
                            <li class="flex items-start gap-2 text-sm">
                                <svg class="w-4 h-4 text-purple-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <div>
                                    <p class="text-gray-800 dark:text-gray-200 leading-tight">{{ $servicio->descripcion }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ \Carbon\Carbon::parse($servicio->fecha_registro)->format('d/m H:i') }}
                                        @if($servicio->usuario_registro) · {{ $servicio->usuario_registro }} @endif
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 italic text-center py-4">
                        Sin servicios adicionales
                    </p>
                @endif

                <div class="mt-3 pt-3 border-t border-purple-200 dark:border-purple-700">
                    <a href="{{ route('servicios-adicionales.index') }}"
                       class="text-xs text-purple-600 dark:text-purple-400 hover:underline font-medium">
                        Gestionar servicios →
                    </a>
                </div>
            </div>

            {{-- ── COLUMNA 3: Estacionamiento / Vehículo ── --}}
            <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700 rounded-lg p-4">
                <h4 class="text-xs font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wide mb-3 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2m0 0h10m-10 0H3m10 0h3m0 0l3-3m-3 3V6a1 1 0 011-1h2.586a1 1 0 01.707.293l2.414 2.414A1 1 0 0121 9.414V16"/>
                    </svg>
                    Vehículo & Estacionamiento
                </h4>

                @if($reserva->estacionamiento_no_espacio)
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 px-3 py-2 rounded-lg">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="font-bold text-sm">Espacio No. {{ $reserva->estacionamiento_no_espacio }}</span>
                        </div>

                        @if($reserva->tipo_vehiculo)
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tipo de vehículo</p>
                            <p class="font-semibold text-gray-900 dark:text-white capitalize">{{ $reserva->tipo_vehiculo }}</p>
                        </div>
                        @endif

                        @if($reserva->descripcion_vehiculo)
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Descripción</p>
                            <p class="text-gray-800 dark:text-gray-200 text-sm bg-white dark:bg-zinc-800 rounded p-2 border border-amber-200 dark:border-amber-700 leading-relaxed">
                                {{ $reserva->descripcion_vehiculo }}
                            </p>
                        </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-6">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-sm text-gray-400 dark:text-gray-500 italic">Sin estacionamiento asignado</p>
                    </div>
                @endif

                <div class="mt-3 pt-3 border-t border-amber-200 dark:border-amber-700">
                    <a href="{{ route('estacionamiento.index') }}"
                       class="text-xs text-amber-600 dark:text-amber-400 hover:underline font-medium">
                        Ver estacionamiento →
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
