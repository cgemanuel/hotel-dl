<?php

namespace App\Livewire\Habitaciones;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

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
        // Estadísticas de la planta activa
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
        // Obtener habitaciones con la reserva MÁS RECIENTE activa
        $this->habitacionesActuales = DB::table('habitaciones')
            ->where('planta', $this->plantaActiva)
            ->leftJoin('habitaciones_has_reservas', 'habitaciones.idhabitacion', '=', 'habitaciones_has_reservas.habitaciones_idhabitacion')
            ->leftJoin('reservas', function($join) {
                $join->on('habitaciones_has_reservas.reservas_idreservas', '=', 'reservas.idreservas')
                     ->whereIn('reservas.estado', ['confirmada', 'pendiente']);
            })
            ->leftJoin('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
            ->select(
                'habitaciones.idhabitacion',
                'habitaciones.no_habitacion',
                'habitaciones.tipo',
                //'habitaciones.precio',
                'habitaciones.estado',
                'habitaciones.planta',
                DB::raw('MAX(reservas.idreservas) as reserva_id')
            )
            ->groupBy(
                'habitaciones.idhabitacion',
                'habitaciones.no_habitacion',
                'habitaciones.tipo',
                //'habitaciones.precio',
                'habitaciones.estado',
                'habitaciones.planta'
            )
            ->orderBy('habitaciones.no_habitacion')
            ->get();
    }

    public function seleccionarHabitacion($habitacionId)
    {
        // Obtener información de la habitación con la reserva actual
        $habitacion = DB::table('habitaciones')
            ->where('habitaciones.idhabitacion', $habitacionId)
            ->leftJoin('habitaciones_has_reservas', 'habitaciones.idhabitacion', '=', 'habitaciones_has_reservas.habitaciones_idhabitacion')
            ->leftJoin('reservas', function($join) {
                $join->on('habitaciones_has_reservas.reservas_idreservas', '=', 'reservas.idreservas')
                     ->whereIn('reservas.estado', ['confirmada', 'pendiente']);
            })
            ->leftJoin('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
            ->select(
                'habitaciones.*',
                DB::raw('MAX(reservas.idreservas) as reserva_id'),
                DB::raw('MAX(clientes.nom_completo) as nom_completo'),
                DB::raw('MAX(clientes.correo) as correo'),
                DB::raw('MAX(reservas.no_personas) as no_personas'),
                DB::raw('MAX(reservas.fecha_check_in) as fecha_check_in'),
                DB::raw('MAX(reservas.fecha_check_out) as fecha_check_out'),
                DB::raw('MAX(reservas.folio) as folio')
            )
            ->groupBy(
                'habitaciones.idhabitacion',
                'habitaciones.no_habitacion',
                'habitaciones.tipo',
                //'habitaciones.precio',
                'habitaciones.estado',
                'habitaciones.planta'
            )
            ->first();

        if ($habitacion) {
            $this->habitacionSeleccionada = (array) $habitacion;
            $this->mostrarModal = true;
            $this->mostrarHistorial = false; // Resetear vista de historial
        }
    }

    public function toggleHistorial()
    {
        $this->mostrarHistorial = !$this->mostrarHistorial;

        if ($this->mostrarHistorial && $this->habitacionSeleccionada) {
            // Cargar historial completo de reservas
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
            // Obtener estado anterior
            $habitacion = DB::table('habitaciones')
                ->where('idhabitacion', $habitacionId)
                ->first();

            // AUDITORÍA: Registrar cambio de estado
            \App\Services\AuditService::logUpdated(
                'Habitacion',
                $habitacionId,
                ['estado' => $habitacion->estado],
                ['estado' => $nuevoEstado]
            );

            DB::table('habitaciones')
                ->where('idhabitacion', $habitacionId)
                ->update(['estado' => $nuevoEstado]);

            session()->flash('message', 'Estado de la habitación actualizado exitosamente.');

            $this->cerrarModal();
            $this->cargarEstadisticas();
            $this->cargarHabitaciones();

        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar el estado: ' . $e->getMessage());
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
