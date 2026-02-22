<?php

namespace App\Livewire\Habitaciones;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CroquisHabitaciones extends Component
{
    public $plantaActiva = 'Planta 1';
    public $plantas = ['Planta 1', 'Planta 2', 'Planta 3'];

    // ── NUEVO: fecha consultada (por defecto hoy) ──
    public $fechaConsulta = '';

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
        $this->fechaConsulta = now()->toDateString();
        $this->cargarEstadisticas();
        $this->cargarHabitaciones();
    }

    public function refrescar()
    {
        $this->cargarEstadisticas();
        $this->cargarHabitaciones();
    }

    // ── Se llama automáticamente cuando cambia la fecha ──
    public function updatedFechaConsulta()
    {
        $this->cargarEstadisticas();
        $this->cargarHabitaciones();
    }

    public function irAHoy()
    {
        $this->fechaConsulta = now()->toDateString();
        $this->cargarEstadisticas();
        $this->cargarHabitaciones();
    }

    /**
     * Devuelve el estado REAL de una habitación para la fecha consultada.
     * - Si está en mantenimiento en la BD → "en_mantenimiento" (siempre)
     * - Si tiene una reserva activa que cubre esa fecha → "ocupada"
     * - De lo contrario → "disponible"
     */
    protected function calcularEstadoParaFecha(int $habitacionId, string $estadoBD): string
    {
        // El mantenimiento tiene prioridad sobre todo
        if (in_array(strtolower(trim($estadoBD)), ['en_mantenimiento', 'mantenimiento'])) {
            return $estadoBD;
        }

        $fecha = $this->fechaConsulta ?: now()->toDateString();

        $tieneReserva = DB::table('habitaciones_has_reservas')
            ->join('reservas', 'habitaciones_has_reservas.reservas_idreservas', '=', 'reservas.idreservas')
            ->where('habitaciones_has_reservas.habitaciones_idhabitacion', $habitacionId)
            ->whereIn('reservas.estado', ['confirmada', 'pendiente', 'ocupada', 'check_in', 'hospedada'])
            ->where('reservas.fecha_check_in', '<=', $fecha)
            ->where('reservas.fecha_check_out', '>=', $fecha)
            ->exists();

        return $tieneReserva ? 'ocupada' : 'disponible';
    }

    public function cargarEstadisticas()
    {
        $fecha = $this->fechaConsulta ?: now()->toDateString();

        // Total habitaciones en esta planta
        $this->totalHabitaciones = DB::table('habitaciones')
            ->where('planta', $this->plantaActiva)
            ->count();

        // Habitaciones en mantenimiento (estado fijo en BD)
        $this->mantenimiento = DB::table('habitaciones')
            ->where('planta', $this->plantaActiva)
            ->whereIn('estado', ['en_mantenimiento', 'mantenimiento'])
            ->count();

        // Habitaciones ocupadas PARA LA FECHA CONSULTADA
        // (tienen reserva activa que cubre esa fecha y NO están en mantenimiento)
        $this->ocupadas = DB::table('habitaciones')
            ->where('planta', $this->plantaActiva)
            ->whereNotIn('estado', ['en_mantenimiento', 'mantenimiento'])
            ->whereIn('idhabitacion', function ($sub) use ($fecha) {
                $sub->select('habitaciones_has_reservas.habitaciones_idhabitacion')
                    ->from('habitaciones_has_reservas')
                    ->join('reservas', 'habitaciones_has_reservas.reservas_idreservas', '=', 'reservas.idreservas')
                    ->whereIn('reservas.estado', ['confirmada', 'pendiente', 'ocupada', 'check_in', 'hospedada'])
                    ->where('reservas.fecha_check_in', '<=', $fecha)
                    ->where('reservas.fecha_check_out', '>=', $fecha);
            })
            ->count();

        // Disponibles = total - mantenimiento - ocupadas
        $this->disponibles = $this->totalHabitaciones - $this->mantenimiento - $this->ocupadas;
        if ($this->disponibles < 0) $this->disponibles = 0;
    }

    public function cambiarPlanta($planta)
    {
        $this->plantaActiva = $planta;
        $this->cargarEstadisticas();
        $this->cargarHabitaciones();
    }

    public function cargarHabitaciones()
    {
        $fecha = $this->fechaConsulta ?: now()->toDateString();

        // Traer habitaciones base
        $habitacionesDB = DB::table('habitaciones')
            ->where('planta', $this->plantaActiva)
            ->orderBy('no_habitacion')
            ->get();

        // Para cada habitación, calcular su estado real para la fecha consultada
        $this->habitacionesActuales = $habitacionesDB->map(function ($hab) use ($fecha) {
            $estadoReal = $this->calcularEstadoParaFecha($hab->idhabitacion, $hab->estado);

            // Obtener reserva activa en esa fecha (si existe)
            $reservaActiva = DB::table('habitaciones_has_reservas')
                ->join('reservas', 'habitaciones_has_reservas.reservas_idreservas', '=', 'reservas.idreservas')
                ->where('habitaciones_has_reservas.habitaciones_idhabitacion', $hab->idhabitacion)
                ->whereIn('reservas.estado', ['confirmada', 'pendiente', 'ocupada', 'check_in', 'hospedada'])
                ->where('reservas.fecha_check_in', '<=', $fecha)
                ->where('reservas.fecha_check_out', '>=', $fecha)
                ->select('reservas.idreservas as reserva_id')
                ->first();

            return (object) [
                'idhabitacion'  => $hab->idhabitacion,
                'no_habitacion' => $hab->no_habitacion,
                'tipo'          => $hab->tipo,
                'estado'        => $estadoReal,         // estado calculado por fecha
                'estado_bd'     => $hab->estado,        // estado real en BD (para mantenimiento)
                'planta'        => $hab->planta,
                'reserva_id'    => $reservaActiva->reserva_id ?? null,
            ];
        })->toArray();
    }

    public function seleccionarHabitacion($habitacionId)
    {
        $fecha = $this->fechaConsulta ?: now()->toDateString();

        $habitacionData = DB::table('habitaciones')
            ->where('habitaciones.idhabitacion', $habitacionId)
            ->select(
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

        // Calcular estado real para la fecha consultada
        $estadoReal = $this->calcularEstadoParaFecha($habitacionId, $habitacionData->estado);

        $this->habitacionSeleccionada = (array) $habitacionData;
        $this->habitacionSeleccionada['estado'] = $estadoReal;
        $this->habitacionSeleccionada['servicios'] = [];

        // Reserva activa para la fecha consultada
        $reservaActiva = DB::table('habitaciones_has_reservas')
            ->join('reservas', 'habitaciones_has_reservas.reservas_idreservas', '=', 'reservas.idreservas')
            ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
            ->where('habitaciones_has_reservas.habitaciones_idhabitacion', $habitacionId)
            ->whereIn('reservas.estado', ['confirmada', 'pendiente', 'ocupada', 'check_in', 'hospedada'])
            ->where('reservas.fecha_check_in', '<=', $fecha)
            ->where('reservas.fecha_check_out', '>=', $fecha)
            ->select(
                'reservas.idreservas',
                'reservas.folio',
                'reservas.no_personas',
                'reservas.fecha_check_in',
                'reservas.fecha_check_out',
                'reservas.tipo_vehiculo as vehiculo_tipo',
                'reservas.descripcion_vehiculo as vehiculo_descripcion',
                'clientes.nom_completo',
            )
            ->orderBy('reservas.idreservas', 'desc')
            ->first();

        if ($reservaActiva) {
            $this->habitacionSeleccionada['idreserva']            = $reservaActiva->idreservas;
            $this->habitacionSeleccionada['folio']                = $reservaActiva->folio;
            $this->habitacionSeleccionada['no_personas']          = $reservaActiva->no_personas;
            $this->habitacionSeleccionada['fecha_check_in']       = $reservaActiva->fecha_check_in;
            $this->habitacionSeleccionada['fecha_check_out']      = $reservaActiva->fecha_check_out;
            $this->habitacionSeleccionada['vehiculo_tipo']        = $reservaActiva->vehiculo_tipo;
            $this->habitacionSeleccionada['vehiculo_descripcion'] = $reservaActiva->vehiculo_descripcion;
            $this->habitacionSeleccionada['nom_completo']         = $reservaActiva->nom_completo;
            // Cargar servicios adicionales
            $servicios = DB::table('servicios_adicionales')
                ->where('reservas_idreservas', $reservaActiva->idreservas)
                ->orderBy('id', 'desc')
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray();

            $this->habitacionSeleccionada['servicios'] = $servicios;
        }

        Log::info('Habitación: ' . $habitacionId . ' | Fecha: ' . $fecha . ' | Estado: ' . $estadoReal);

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
