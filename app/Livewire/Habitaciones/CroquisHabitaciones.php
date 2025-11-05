<?php

namespace App\Livewire\Habitaciones;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CroquisHabitaciones extends Component
{
    public $habitacionesPorPlanta = [];
    public $habitacionSeleccionada = null;
    public $mostrarModal = false;
    public $plantaActiva = 'Planta 1';

    protected $listeners = ['reserva-actualizada' => '$refresh', 'habitacion-estado-cambiado' => 'cargarHabitaciones'];

    public function mount()
    {
        $this->cargarHabitaciones();
    }

    public function cargarHabitaciones()
    {
        // Obtener todas las plantas únicas
        $plantas = DB::table('habitaciones')
            ->distinct()
            ->orderBy('planta')
            ->pluck('planta')
            ->toArray();

        // Para cada planta, obtener sus habitaciones con info de reservas
        foreach ($plantas as $planta) {
            $habitaciones = DB::table('habitaciones')
                ->leftJoin('habitaciones_has_reservas', 'habitaciones.idhabitacion', '=', 'habitaciones_has_reservas.habitaciones_idhabitacion')
                ->leftJoin('reservas', function($join) {
                    $join->on('habitaciones_has_reservas.reservas_idreservas', '=', 'reservas.idreservas')
                        ->where('reservas.estado', '=', 'confirmada')
                        ->where('reservas.fecha_check_out', '>=', now()->toDateString());
                })
                ->leftJoin('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
                ->where('habitaciones.planta', $planta)
                ->select(
                    'habitaciones.idhabitacion',
                    'habitaciones.no_habitacion',
                    'habitaciones.tipo',
                    'habitaciones.precio',
                    'habitaciones.estado',
                    'habitaciones.planta',
                    'clientes.nom_completo',
                    'clientes.telefono',
                    'clientes.correo',
                    'reservas.fecha_check_in',
                    'reservas.fecha_check_out',
                    'reservas.no_personas'
                )
                ->orderBy('habitaciones.no_habitacion')
                ->get()
                ->toArray();

            $this->habitacionesPorPlanta[$planta] = $habitaciones;
        }

        // Si no hay planta activa o no existe, asignar la primera
        if (count($plantas) > 0) {
            if (!in_array($this->plantaActiva, $plantas)) {
                $this->plantaActiva = $plantas[0];
            }
        }
    }

    public function seleccionarHabitacion($idhabitacion)
    {
        $habitacion = collect($this->habitacionesPorPlanta[$this->plantaActiva] ?? [])
            ->firstWhere('idhabitacion', $idhabitacion);

        if ($habitacion) {
            $this->habitacionSeleccionada = (array) $habitacion;
            $this->mostrarModal = true;
        }
    }

    public function cambiarPlanta($planta)
    {
        $this->plantaActiva = $planta;
        $this->mostrarModal = false;
        $this->habitacionSeleccionada = null;
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->habitacionSeleccionada = null;
    }

    public function cambiarEstadoHabitacion($idhabitacion, $nuevoEstado)
    {
        try {
            DB::table('habitaciones')
                ->where('idhabitacion', $idhabitacion)
                ->update(['estado' => $nuevoEstado]);

            session()->flash('message', 'Estado de la habitación actualizado exitosamente.');

            $this->cargarHabitaciones();
            $this->cerrarModal();

            $this->dispatch('habitacion-estado-cambiado');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cambiar el estado: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $plantas = array_keys($this->habitacionesPorPlanta);
        $habitacionesActuales = $this->habitacionesPorPlanta[$this->plantaActiva] ?? [];

        $totalHabitaciones = count($habitacionesActuales);
        $disponibles = collect($habitacionesActuales)->where('estado', 'disponible')->count();
        $ocupadas = collect($habitacionesActuales)->where('estado', 'ocupada')->count();
        $mantenimiento = collect($habitacionesActuales)->where('estado', 'en_mantenimiento')->count();

        return view('livewire.habitaciones.croquis-habitaciones', [
            'plantas' => $plantas,
            'habitacionesActuales' => $habitacionesActuales,
            'totalHabitaciones' => $totalHabitaciones,
            'disponibles' => $disponibles,
            'ocupadas' => $ocupadas,
            'mantenimiento' => $mantenimiento,
        ]);
    }
}
