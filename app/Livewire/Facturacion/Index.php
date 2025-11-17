<?php

namespace App\Livewire\Facturacion;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $estado_filtro = '';
    public $editando_facturacion = null;
    public $facturacion_texto = '';

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

    public function editarFacturacion($reservaId, $facturacionActual)
    {
        $this->editando_facturacion = $reservaId;
        $this->facturacion_texto = $facturacionActual ?? '';
    }

    public function guardarFacturacion($reservaId)
    {
        try {
            DB::table('reservas')
                ->where('idreservas', $reservaId)
                ->update([
                    'facturacion' => $this->facturacion_texto,
                    'updated_at' => now()
                ]);

            session()->flash('message', 'Información de facturación actualizada exitosamente.');

            $this->editando_facturacion = null;
            $this->facturacion_texto = '';
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function cancelarEdicion()
    {
        $this->editando_facturacion = null;
        $this->facturacion_texto = '';
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
                'reservas.estado',
                'reservas.facturacion',
                'clientes.nom_completo',
                'clientes.correo',
                DB::raw('MAX(habitaciones.no_habitacion) as no_habitacion')
            )
            ->groupBy(
                'reservas.idreservas',
                'reservas.folio',
                'reservas.estado',
                'reservas.facturacion',
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

        $reservas = $query->orderBy('reservas.idreservas', 'desc')->paginate(15);

        return view('livewire.facturacion.index', [
            'reservas' => $reservas
        ]);
    }
}
