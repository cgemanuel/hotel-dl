<?php

namespace App\Livewire\ServiciosAdicionales;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $estado_filtro = '';

    // Para mostrar modal de servicios
    public $mostrarModalServicios = false;
    public $reserva_seleccionada = null;
    public $servicios_reserva = [];

    // Para agregar nuevo servicio
    public $nuevo_servicio = '';

    protected $queryString = ['search', 'estado_filtro'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'estado_filtro']);
        $this->resetPage();
    }

    /**
     * Abrir modal para ver/agregar servicios
     */
    public function abrirModalServicios($reservaId)
    {
        $this->reserva_seleccionada = $reservaId;
        $this->cargarServiciosReserva();
        $this->mostrarModalServicios = true;
    }

    /**
     * Cargar servicios existentes de la reserva
     */
    public function cargarServiciosReserva()
    {
        $this->servicios_reserva = DB::table('servicios_adicionales')
            ->where('reservas_idreservas', $this->reserva_seleccionada)
            ->orderBy('fecha_registro', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Agregar nuevo servicio
     */
    public function agregarServicio()
    {
        $this->validate([
            'nuevo_servicio' => 'required|string|max:500',
        ], [
            'nuevo_servicio.required' => 'Debes ingresar la descripción del servicio',
            'nuevo_servicio.max' => 'La descripción no puede exceder 500 caracteres',
        ]);

        try {
            DB::table('servicios_adicionales')->insert([
                'reservas_idreservas' => $this->reserva_seleccionada,
                'descripcion' => trim($this->nuevo_servicio),
                'fecha_registro' => now(),
                'usuario_registro' => Auth::user()->name ?? 'Sistema',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            session()->flash('message', 'Servicio agregado exitosamente.');

            // Limpiar y recargar
            $this->reset('nuevo_servicio');
            $this->cargarServiciosReserva();

        } catch (\Exception $e) {
            session()->flash('error', 'Error al agregar servicio: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar servicio
     */
    public function eliminarServicio($servicioId)
    {
        try {
            DB::table('servicios_adicionales')
                ->where('id', $servicioId)
                ->delete();

            session()->flash('message', 'Servicio eliminado.');
            $this->cargarServiciosReserva();

        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Cerrar modal
     */
    public function cerrarModal()
    {
        $this->mostrarModalServicios = false;
        $this->reset(['reserva_seleccionada', 'servicios_reserva', 'nuevo_servicio']);
    }

    public function render()
    {
        $query = DB::table('reservas')
            ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
            ->leftJoin('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
            ->leftJoin('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
            ->select(
                'reservas.idreservas',
                'reservas.folio',
                'reservas.fecha_check_in',
                'reservas.fecha_check_out',
                'reservas.estado',
                'clientes.nom_completo',
                'clientes.correo',
                DB::raw('MAX(habitaciones.no_habitacion) as no_habitacion'),
                DB::raw('(SELECT COUNT(*) FROM servicios_adicionales WHERE servicios_adicionales.reservas_idreservas = reservas.idreservas) as total_servicios')
            )
            ->groupBy(
                'reservas.idreservas',
                'reservas.folio',
                'reservas.fecha_check_in',
                'reservas.fecha_check_out',
                'reservas.estado',
                'clientes.nom_completo',
                'clientes.correo'
            )
            ->whereIn('reservas.estado', ['confirmada', 'completada']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('clientes.nom_completo', 'like', '%' . $this->search . '%')
                  ->orWhere('reservas.folio', 'like', '%' . $this->search . '%')
                  ->orWhere('clientes.correo', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->estado_filtro) {
            $query->where('reservas.estado', $this->estado_filtro);
        }

        $reservas = $query->orderBy('reservas.fecha_check_in', 'desc')->paginate(15);

        return view('livewire.servicios-adicionales.index', [
            'reservas' => $reservas
        ]);
    }
}
