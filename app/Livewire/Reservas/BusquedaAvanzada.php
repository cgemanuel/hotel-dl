<?php

namespace App\Livewire\Reservas;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class BusquedaAvanzada extends Component
{
    use WithPagination;

    // Filtros de búsqueda
    public $folio = '';
    public $nombre_cliente = '';
    public $correo = '';
    public $telefono = '';
    public $no_habitacion = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $estado = '';
    public $plataforma = '';
    public $metodo_pago = '';
    public $rango_monto_min = '';
    public $rango_monto_max = '';
    public $tiene_estacionamiento = '';
    public $tipo_habitacion = '';

    public $mostrarFiltros = true;

    protected $queryString = [
        'folio', 'nombre_cliente', 'correo', 'telefono', 'no_habitacion',
        'fecha_inicio', 'fecha_fin', 'estado', 'plataforma', 'metodo_pago',
        'rango_monto_min', 'rango_monto_max', 'tiene_estacionamiento', 'tipo_habitacion'
    ];

    public function updatingFolio() { $this->resetPage(); }
    public function updatingNombreCliente() { $this->resetPage(); }
    public function updatingCorreo() { $this->resetPage(); }
    public function updatingTelefono() { $this->resetPage(); }
    public function updatingNoHabitacion() { $this->resetPage(); }
    public function updatingFechaInicio() { $this->resetPage(); }
    public function updatingFechaFin() { $this->resetPage(); }
    public function updatingEstado() { $this->resetPage(); }
    public function updatingPlataforma() { $this->resetPage(); }
    public function updatingMetodoPago() { $this->resetPage(); }
    public function updatingTieneEstacionamiento() { $this->resetPage(); }
    public function updatingTipoHabitacion() { $this->resetPage(); }

    public function limpiarFiltros()
    {
        $this->reset([
            'folio', 'nombre_cliente', 'correo', 'telefono', 'no_habitacion',
            'fecha_inicio', 'fecha_fin', 'estado', 'plataforma', 'metodo_pago',
            'rango_monto_min', 'rango_monto_max', 'tiene_estacionamiento', 'tipo_habitacion'
        ]);
        $this->resetPage();
    }

    public function exportarExcel()
    {
        // Implementar exportación a Excel
        session()->flash('message', 'Exportación iniciada...');
    }

    public function render()
    {
        $query = DB::table('reservas')
            ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
            ->leftJoin('plat_reserva', 'reservas.plat_reserva_idplat_reserva', '=', 'plat_reserva.idplat_reserva')
            ->leftJoin('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
            ->leftJoin('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
            ->select(
                'reservas.*',
                'clientes.nom_completo',
                'clientes.correo',
                'clientes.telefono',
                'plat_reserva.nombre_plataforma',
                DB::raw('MAX(habitaciones.no_habitacion) as no_habitacion'),
                DB::raw('MAX(habitaciones.tipo) as tipo_habitacion'),
                DB::raw('MAX(habitaciones.precio) as precio')
            )
            ->groupBy(
                'reservas.idreservas', 'reservas.folio', 'reservas.fecha_reserva',
                'reservas.fecha_check_in', 'reservas.fecha_check_out', 'reservas.no_personas',
                'reservas.estado', 'reservas.metodo_pago', 'reservas.monto_efectivo',
                'reservas.monto_tarjeta', 'reservas.monto_transferencia',
                'reservas.estacionamiento_no_espacio', 'reservas.plat_reserva_idplat_reserva',
                'reservas.clientes_idclientes', 'reservas.created_by', 'reservas.created_at',
                'reservas.updated_at', 'reservas.facturacion',
                'clientes.nom_completo', 'clientes.correo', 'clientes.telefono',
                'plat_reserva.nombre_plataforma'
            );

        // Aplicar filtros
        if ($this->folio) {
            $query->where('reservas.folio', 'like', '%' . $this->folio . '%');
        }

        if ($this->nombre_cliente) {
            $query->where('clientes.nom_completo', 'like', '%' . $this->nombre_cliente . '%');
        }

        if ($this->correo) {
            $query->where('clientes.correo', 'like', '%' . $this->correo . '%');
        }

        if ($this->telefono) {
            $query->where('clientes.telefono', 'like', '%' . $this->telefono . '%');
        }

        if ($this->no_habitacion) {
            $query->having('no_habitacion', '=', $this->no_habitacion);
        }

        if ($this->fecha_inicio && $this->fecha_fin) {
            $query->whereBetween('reservas.fecha_check_in', [$this->fecha_inicio, $this->fecha_fin]);
        } elseif ($this->fecha_inicio) {
            $query->where('reservas.fecha_check_in', '>=', $this->fecha_inicio);
        } elseif ($this->fecha_fin) {
            $query->where('reservas.fecha_check_in', '<=', $this->fecha_fin);
        }

        if ($this->estado) {
            $query->where('reservas.estado', $this->estado);
        }

        if ($this->plataforma) {
            $query->where('reservas.plat_reserva_idplat_reserva', $this->plataforma);
        }

        if ($this->metodo_pago) {
            $query->where('reservas.metodo_pago', $this->metodo_pago);
        }

        if ($this->tiene_estacionamiento === 'si') {
            $query->whereNotNull('reservas.estacionamiento_no_espacio');
        } elseif ($this->tiene_estacionamiento === 'no') {
            $query->whereNull('reservas.estacionamiento_no_espacio');
        }

        if ($this->tipo_habitacion) {
            $query->having('tipo_habitacion', '=', $this->tipo_habitacion);
        }

        $reservas = $query->orderBy('reservas.idreservas', 'desc')->paginate(20);

        // Obtener datos para los selectores
        $plataformas = DB::table('plat_reserva')->get();

        return view('livewire.reservas.busqueda-avanzada', [
            'reservas' => $reservas,
            'plataformas' => $plataformas,
        ]);
    }
}
