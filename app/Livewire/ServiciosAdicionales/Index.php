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
    public $editando_servicio = null;
    public $servicio_texto = '';

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

    public function editarServicio($reservaId, $servicioActual)
    {
        $this->editando_servicio = $reservaId;
        $this->servicio_texto = $servicioActual ?? '';
    }

    public function guardarServicio($reservaId)
    {
        $this->validate([
            'servicio_texto' => 'nullable|string|max:2000', // Límite de 2000 caracteres
        ], [
            'servicio_texto.max' => 'La descripción no puede exceder 2000 caracteres.'
        ]);

        try {
            DB::beginTransaction();

            // Verificar si ya existe un registro de servicios para esta reserva
            $servicioExistente = DB::table('servicios_adicionales')
                ->where('reservas_idreservas', $reservaId)
                ->first();

            if ($servicioExistente) {
                // Actualizar servicio existente
                if (empty(trim($this->servicio_texto))) {
                    // Si el texto está vacío, eliminar el registro
                    DB::table('servicios_adicionales')
                        ->where('id_servicio', $servicioExistente->id_servicio)
                        ->delete();
                } else {
                    // Actualizar el registro
                    DB::table('servicios_adicionales')
                        ->where('id_servicio', $servicioExistente->id_servicio)
                        ->update([
                            'descripcion' => trim($this->servicio_texto),
                            'fecha_registro' => now(),
                            'usuario_registro' => Auth::user()->name ?? 'Sistema',
                        ]);
                }
            } else {
                // Crear nuevo registro solo si hay texto
                if (!empty(trim($this->servicio_texto))) {
                    DB::table('servicios_adicionales')->insert([
                        'reservas_idreservas' => $reservaId,
                        'descripcion' => trim($this->servicio_texto),
                        'fecha_registro' => now(),
                        'usuario_registro' => Auth::user()->name ?? 'Sistema',
                    ]);
                }
            }

            DB::commit();

            session()->flash('message', 'Servicios adicionales actualizados exitosamente.');

            $this->editando_servicio = null;
            $this->servicio_texto = '';
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function cancelarEdicion()
    {
        $this->editando_servicio = null;
        $this->servicio_texto = '';
    }

    public function render()
    {
        $query = DB::table('reservas')
            ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
            ->leftJoin('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
            ->leftJoin('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
            ->leftJoin('servicios_adicionales', 'reservas.idreservas', '=', 'servicios_adicionales.reservas_idreservas')
            ->select(
                'reservas.idreservas',
                'reservas.folio',
                'reservas.fecha_check_in',
                'reservas.fecha_check_out',
                'reservas.estado',
                'clientes.nom_completo',
                'clientes.correo',
                DB::raw('MAX(habitaciones.no_habitacion) as no_habitacion'),
                DB::raw('MAX(servicios_adicionales.descripcion) as servicios_adicionales')
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
