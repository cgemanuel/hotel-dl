<?php

namespace App\Livewire\Reservas;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $estado_filtro = '';

    public $mostrarModalVer = false;
    public $mostrarModalEditar = false;
    public $reservaSeleccionada = null;

    // Datos para editar
    public $editando_id;
    public $edit_fecha_reserva;
    public $edit_fecha_check_in;
    public $edit_fecha_check_out;
    public $edit_no_personas;
    public $edit_estado;
    public $edit_estacionamiento_no_espacio;

    protected $queryString = ['search', 'fecha_inicio', 'fecha_fin', 'estado_filtro'];
    protected $listeners = ['reserva-creada' => '$refresh', 'reserva-eliminada' => '$refresh', 'reserva-actualizada' => '$refresh'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFechaInicio()
    {
        $this->resetPage();
    }

    public function updatingFechaFin()
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'fecha_inicio', 'fecha_fin', 'estado_filtro']);
        $this->resetPage();
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
                'clientes.telefono',
                'clientes.correo',
                'plat_reserva.nombre_plataforma',
                'plat_reserva.comision',
                'habitaciones.precio',
                'habitaciones.no_habitacion',
                'habitaciones.tipo as tipo_habitacion'
            );

        // Filtro por búsqueda (nombre, folio o ID)
        if ($this->search) {
            $query->where(function($q) {
                $q->where('clientes.nom_completo', 'like', '%' . $this->search . '%')
                  ->orWhere('reservas.folio', 'like', '%' . $this->search . '%')
                  ->orWhere('reservas.idreservas', 'like', '%' . $this->search . '%');
            });
        }

        // Filtro por rango de fechas
        if ($this->fecha_inicio && $this->fecha_fin) {
            $query->whereBetween('reservas.fecha_check_in', [$this->fecha_inicio, $this->fecha_fin]);
        } elseif ($this->fecha_inicio) {
            $query->where('reservas.fecha_check_in', '>=', $this->fecha_inicio);
        } elseif ($this->fecha_fin) {
            $query->where('reservas.fecha_check_in', '<=', $this->fecha_fin);
        }

        // Filtro por estado
        if ($this->estado_filtro) {
            $query->where('reservas.estado', $this->estado_filtro);
        }

        $reservas = $query->orderBy('reservas.idreservas', 'desc')->paginate(10);

        // Calcular totales para cada reserva
        foreach ($reservas as $reserva) {
            $reserva->total_calculado = $this->calcularTotal($reserva);
        }

        return view('livewire.reservas.index', [
            'reservas' => $reservas
        ]);
    }

    // Método para calcular el total
    private function calcularTotal($reserva)
    {
        $checkIn = Carbon::parse($reserva->fecha_check_in);
        $checkOut = Carbon::parse($reserva->fecha_check_out);
        $dias = $checkOut->diffInDays($checkIn);

        $subtotal = ($reserva->precio ?? 0) * $dias;
        $comision = ($reserva->comision ?? 0) / 100;
        $montoComision = $subtotal * $comision;
        $total = $subtotal + $montoComision;

        return [
            'dias' => $dias,
            'precio_noche' => $reserva->precio ?? 0,
            'subtotal' => $subtotal,
            'comision_porcentaje' => $reserva->comision ?? 0,
            'monto_comision' => $montoComision,
            'total' => $total
        ];
    }

    public function ver($id)
    {
        try {
            $this->reservaSeleccionada = DB::table('reservas')
                ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
                ->leftJoin('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
                ->leftJoin('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
                ->leftJoin('plat_reserva', 'reservas.plat_reserva_idplat_reserva', '=', 'plat_reserva.idplat_reserva')
                ->leftJoin('estacionamiento', 'reservas.estacionamiento_no_espacio', '=', 'estacionamiento.no_espacio')
                ->where('reservas.idreservas', $id)
                ->select(
                    'reservas.*',
                    'clientes.nom_completo',
                    'clientes.telefono',
                    'clientes.correo',
                    'clientes.tipo_identificacion',
                    'clientes.no_identificacion',
                    'clientes.edad',
                    'clientes.direccion',
                    'clientes.estado_origen',
                    'clientes.pais_origen',
                    'habitaciones.no_habitacion',
                    'habitaciones.tipo as tipo_habitacion',
                    'habitaciones.precio',
                    'plat_reserva.nombre_plataforma',
                    'plat_reserva.comision',
                    'estacionamiento.no_espacio'
                )
                ->first();

            if ($this->reservaSeleccionada) {
                $this->reservaSeleccionada->total_calculado = $this->calcularTotal($this->reservaSeleccionada);
                $this->mostrarModalVer = true;
            } else {
                session()->flash('error', 'Reserva no encontrada.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar la reserva: ' . $e->getMessage());
        }
    }

    public function cerrarModalVer()
    {
        $this->mostrarModalVer = false;
        $this->reservaSeleccionada = null;
    }

    public function editar($id)
    {
        try {
            $this->reservaSeleccionada = DB::table('reservas')
                ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
                ->where('reservas.idreservas', $id)
                ->select('reservas.*', 'clientes.nom_completo')
                ->first();

            if ($this->reservaSeleccionada) {
                $this->editando_id = $this->reservaSeleccionada->idreservas;
                $this->edit_fecha_reserva = $this->reservaSeleccionada->fecha_reserva;
                $this->edit_fecha_check_in = $this->reservaSeleccionada->fecha_check_in;
                $this->edit_fecha_check_out = $this->reservaSeleccionada->fecha_check_out;
                $this->edit_no_personas = $this->reservaSeleccionada->no_personas;
                $this->edit_estado = $this->reservaSeleccionada->estado;
                $this->edit_estacionamiento_no_espacio = $this->reservaSeleccionada->estacionamiento_no_espacio;
                $this->mostrarModalEditar = true;
            } else {
                session()->flash('error', 'Reserva no encontrada.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar la reserva: ' . $e->getMessage());
        }
    }

    public function cerrarModalEditar()
    {
        $this->mostrarModalEditar = false;
        $this->reset(['editando_id', 'edit_fecha_reserva', 'edit_fecha_check_in', 'edit_fecha_check_out', 'edit_no_personas', 'edit_estado', 'edit_estacionamiento_no_espacio']);
    }

    public function actualizarReserva()
    {
        $this->validate([
            'edit_fecha_reserva' => 'required|date',
            'edit_fecha_check_in' => 'required|date',
            'edit_fecha_check_out' => 'required|date|after:edit_fecha_check_in',
            'edit_no_personas' => 'required|integer|min:1',
            'edit_estado' => 'required|in:pendiente,confirmada,cancelada,completada',
        ]);

        try {
            DB::table('reservas')
                ->where('idreservas', $this->editando_id)
                ->update([
                    'fecha_reserva' => $this->edit_fecha_reserva,
                    'fecha_check_in' => $this->edit_fecha_check_in,
                    'fecha_check_out' => $this->edit_fecha_check_out,
                    'no_personas' => $this->edit_no_personas,
                    'estado' => $this->edit_estado,
                    'estacionamiento_no_espacio' => $this->edit_estacionamiento_no_espacio,
                ]);

            session()->flash('message', 'Reserva actualizada exitosamente.');
            $this->cerrarModalEditar();
            $this->dispatch('reserva-actualizada');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar la reserva: ' . $e->getMessage());
        }
    }

    public function eliminar($id)
    {
        try {
            DB::beginTransaction();

            $reserva = DB::table('reservas')->where('idreservas', $id)->first();

            if ($reserva) {
                DB::table('reservas')
                    ->where('idreservas', $id)
                    ->update(['estado' => 'cancelada']);

                $habitacion = DB::table('habitaciones_has_reservas')
                    ->where('reservas_idreservas', $id)
                    ->first();

                if ($habitacion) {
                    DB::table('habitaciones')
                        ->where('idhabitacion', $habitacion->habitaciones_idhabitacion)
                        ->update(['estado' => 'disponible']);
                }

                if ($reserva->estacionamiento_no_espacio) {
                    DB::table('estacionamiento')
                        ->where('no_espacio', $reserva->estacionamiento_no_espacio)
                        ->update(['estado' => 'disponible']);
                }

                DB::commit();
                session()->flash('message', 'Reserva cancelada exitosamente.');
            } else {
                session()->flash('error', 'Reserva no encontrada.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al cancelar la reserva: ' . $e->getMessage());
        }
    }

    public function liberar($id)
    {
        try {
            DB::beginTransaction();

            $reserva = DB::table('reservas')->where('idreservas', $id)->first();

            if ($reserva) {
                // Verificar que la fecha de checkout haya pasado o sea hoy
                $fechaCheckout = Carbon::parse($reserva->fecha_check_out);
                if ($fechaCheckout->isFuture()) {
                    session()->flash('warning', 'No se puede liberar la reserva antes de la fecha de check-out.');
                    return;
                }

                // Cambiar estado a completada
                DB::table('reservas')
                    ->where('idreservas', $id)
                    ->update(['estado' => 'completada']);

                // Liberar habitaciones
                $habitacion = DB::table('habitaciones_has_reservas')
                    ->where('reservas_idreservas', $id)
                    ->first();

                if ($habitacion) {
                    DB::table('habitaciones')
                        ->where('idhabitacion', $habitacion->habitaciones_idhabitacion)
                        ->update(['estado' => 'disponible']);
                }

                // Liberar estacionamiento
                if ($reserva->estacionamiento_no_espacio) {
                    DB::table('estacionamiento')
                        ->where('no_espacio', $reserva->estacionamiento_no_espacio)
                        ->update(['estado' => 'disponible']);
                }

                DB::commit();
                session()->flash('message', 'Reserva liberada exitosamente.');
                $this->dispatch('reserva-actualizada');
            } else {
                session()->flash('error', 'Reserva no encontrada.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al liberar la reserva: ' . $e->getMessage());
        }
    }
}
