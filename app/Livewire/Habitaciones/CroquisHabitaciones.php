<?php

namespace App\Livewire\Habitaciones;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CroquisHabitaciones extends Component
{
    public $plantaActiva = 'Planta 1';
    public $plantas = ['Planta 1', 'Planta 2', 'Planta 3'];

    public $totalHabitaciones = 0;
    public $disponibles = 0;
    public $ocupadas = 0;
    public $mantenimiento = 0;

    public $habitacionesActuales = [];
    public $habitacionSeleccionada = null;
    public $mostrarModal = false;
    public $mostrarHistorial = false;
    public $historialReservas = [];

    protected $listeners = ['reserva-actualizada' => 'refrescar', 'reserva-creada' => 'refrescar'];

    public function mount()
    {
        $this->cargarEstadisticas();
        $this->cargarHabitaciones();
    }

    public function refrescar()
    {
        $this->cargarEstadisticas();
        $this->cargarHabitaciones();
    }

    public function cargarEstadisticas()
    {
        $this->totalHabitaciones = DB::table('habitaciones')
            ->where('planta', $this->plantaActiva)
            ->count();

        $this->disponibles = DB::table('habitaciones')
            ->where('planta', $this->plantaActiva)
            ->where('estado', 'disponible')
            ->count();

        $this->ocupadas = DB::table('habitaciones')
            ->where('planta', $this->plantaActiva)
            ->where('estado', 'ocupada')
            ->count();

        $this->mantenimiento = DB::table('habitaciones')
            ->where('planta', $this->plantaActiva)
            ->whereIn('estado', ['en_mantenimiento', 'mantenimiento'])
            ->count();
    }

    public function cambiarPlanta($planta)
    {
        $this->plantaActiva = $planta;
        $this->cargarEstadisticas();
        $this->cargarHabitaciones();
    }

    public function cargarHabitaciones()
    {
        $this->habitacionesActuales = DB::table('habitaciones')
            ->where('planta', $this->plantaActiva)
            ->leftJoin('habitaciones_has_reservas', 'habitaciones.idhabitacion', '=', 'habitaciones_has_reservas.habitaciones_idhabitacion')
            ->leftJoin('reservas', function($join) {
                $join->on('habitaciones_has_reservas.reservas_idreservas', '=', 'reservas.idreservas')
                     ->whereIn('reservas.estado', ['confirmada', 'pendiente', 'ocupada', 'check_in', 'hospedada']);
            })
            ->select(
                'habitaciones.idhabitacion',
                'habitaciones.no_habitacion',
                'habitaciones.tipo',
                'habitaciones.estado',
                'habitaciones.planta',
                DB::raw('MAX(reservas.idreservas) as reserva_id')
            )
            ->groupBy(
                'habitaciones.idhabitacion',
                'habitaciones.no_habitacion',
                'habitaciones.tipo',
                'habitaciones.estado',
                'habitaciones.planta'
            )
            ->orderBy('habitaciones.no_habitacion')
            ->get();
    }

    public function seleccionarHabitacion($habitacionId)
    {
        // ─────────────────────────────────────────────────────────────
        // PASO 1: Datos básicos de habitación + reserva activa + cliente
        // ─────────────────────────────────────────────────────────────
        $habitacionData = DB::table('habitaciones')
            ->where('habitaciones.idhabitacion', $habitacionId)
            ->leftJoin('habitaciones_has_reservas', 'habitaciones.idhabitacion', '=', 'habitaciones_has_reservas.habitaciones_idhabitacion')
            ->leftJoin('reservas', function($join) {
                $join->on('habitaciones_has_reservas.reservas_idreservas', '=', 'reservas.idreservas')
                     ->whereIn('reservas.estado', ['confirmada', 'pendiente', 'ocupada', 'check_in', 'hospedada']);
            })
            ->leftJoin('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
            ->select(
                'habitaciones.idhabitacion',
                'habitaciones.no_habitacion',
                'habitaciones.tipo',
                'habitaciones.estado',
                'habitaciones.planta',
                DB::raw('MAX(reservas.idreservas) as idreserva'),
                DB::raw('MAX(reservas.folio) as folio'),
                DB::raw('MAX(reservas.no_personas) as no_personas'),
                DB::raw('MAX(reservas.fecha_check_in) as fecha_check_in'),
                DB::raw('MAX(reservas.fecha_check_out) as fecha_check_out'),
                DB::raw('MAX(reservas.tipo_vehiculo) as vehiculo_tipo'),
                DB::raw('MAX(reservas.descripcion_vehiculo) as vehiculo_descripcion'),
                DB::raw('MAX(clientes.nom_completo) as nom_completo'),
                DB::raw('MAX(clientes.correo) as correo')
            )
            ->groupBy(
                'habitaciones.idhabitacion',
                'habitaciones.no_habitacion',
                'habitaciones.tipo',
                'habitaciones.estado',
                'habitaciones.planta'
            )
            ->first();

        if (!$habitacionData) {
            return;
        }

        $this->habitacionSeleccionada = (array) $habitacionData;
        $this->habitacionSeleccionada['servicios'] = [];

        // ─────────────────────────────────────────────────────────────
        // PASO 2: Obtener el idreservas REAL de forma directa
        //         (el MAX() a veces puede no coincidir si hay varias
        //          reservas; esta query lo obtiene de manera explícita)
        // ─────────────────────────────────────────────────────────────
        $reservaActiva = DB::table('habitaciones_has_reservas')
            ->join('reservas', 'habitaciones_has_reservas.reservas_idreservas', '=', 'reservas.idreservas')
            ->where('habitaciones_has_reservas.habitaciones_idhabitacion', $habitacionId)
            ->whereIn('reservas.estado', ['confirmada', 'pendiente', 'ocupada', 'check_in', 'hospedada'])
            ->orderBy('reservas.idreservas', 'desc')
            ->select('reservas.idreservas')
            ->first();

        // Sobreescribimos idreserva con el valor verificado
        if ($reservaActiva) {
            $this->habitacionSeleccionada['idreserva'] = $reservaActiva->idreservas;
        }

        Log::info('Habitación: ' . $habitacionId . ' | idreserva resuelto: ' . ($this->habitacionSeleccionada['idreserva'] ?? 'NULL'));

        // ─────────────────────────────────────────────────────────────
        // PASO 3: Cargar servicios usando el ID verificado
        // ─────────────────────────────────────────────────────────────
        if (!empty($this->habitacionSeleccionada['idreserva'])) {

            $servicios = DB::table('servicios_adicionales')
                ->where('reservas_idreservas', $this->habitacionSeleccionada['idreserva'])
                ->orderBy('id', 'desc')
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray();

            $this->habitacionSeleccionada['servicios'] = $servicios;

            Log::info('Servicios encontrados: ' . count($servicios) . ' para reserva ID: ' . $this->habitacionSeleccionada['idreserva']);
        }

        $this->mostrarModal = true;
        $this->mostrarHistorial = false;
    }

    public function toggleHistorial()
    {
        $this->mostrarHistorial = !$this->mostrarHistorial;

        if ($this->mostrarHistorial && $this->habitacionSeleccionada) {
            $this->historialReservas = DB::table('habitaciones_has_reservas')
                ->join('reservas', 'habitaciones_has_reservas.reservas_idreservas', '=', 'reservas.idreservas')
                ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
                ->leftJoin('plat_reserva', 'reservas.plat_reserva_idplat_reserva', '=', 'plat_reserva.idplat_reserva')
                ->where('habitaciones_has_reservas.habitaciones_idhabitacion', $this->habitacionSeleccionada['idhabitacion'])
                ->select(
                    'reservas.folio',
                    'reservas.fecha_check_in',
                    'reservas.fecha_check_out',
                    'reservas.no_personas',
                    'reservas.estado',
                    'clientes.nom_completo',
                    'clientes.correo',
                    'clientes.telefono',
                    'plat_reserva.nombre_plataforma'
                )
                ->orderBy('reservas.fecha_check_in', 'desc')
                ->get();
        }
    }

    public function cambiarEstadoHabitacion($habitacionId, $nuevoEstado)
    {
        try {
            $habitacion = DB::table('habitaciones')->where('idhabitacion', $habitacionId)->first();

            if (class_exists(\App\Services\AuditService::class)) {
                \App\Services\AuditService::logUpdated(
                    'Habitacion',
                    $habitacionId,
                    ['estado' => $habitacion->estado],
                    ['estado' => $nuevoEstado]
                );
            }

            DB::table('habitaciones')
                ->where('idhabitacion', $habitacionId)
                ->update(['estado' => $nuevoEstado]);

            session()->flash('message', 'Estado actualizado.');
            $this->cerrarModal();
            $this->cargarEstadisticas();
            $this->cargarHabitaciones();

        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->habitacionSeleccionada = null;
        $this->mostrarHistorial = false;
        $this->historialReservas = [];
    }

    public function render()
    {
        return view('livewire.habitaciones.croquis-habitaciones');
    }
}
