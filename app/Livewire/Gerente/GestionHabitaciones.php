<?php

namespace App\Livewire\Gerente;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class GestionHabitaciones extends Component
{
    use WithPagination;

    public $search = '';
    public $mostrarModal = false;
    public $editando_id = null;

    public $no_habitacion;
    public $tipo;
    public $precio;
    public $estado;
    public $planta;

    protected $rules = [
        'no_habitacion' => 'required|integer|unique:habitaciones,no_habitacion',
        'tipo' => 'required|string|in:individual,doble,suite',
        'precio' => 'required|numeric|min:0',
        'estado' => 'required|string|in:disponible,ocupada,en_mantenimiento',
        'planta' => 'required|string|in:Planta 1,Planta 2,Planta 3',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function abrirModalCrear()
    {
        $this->reset(['no_habitacion', 'tipo', 'precio', 'estado', 'planta', 'editando_id']);
        $this->mostrarModal = true;
    }

    public function abrirModalEditar($habitacionId)
    {
        $habitacion = DB::table('habitaciones')->where('idhabitacion', $habitacionId)->first();

        if ($habitacion) {
            $this->editando_id = $habitacion->idhabitacion;
            $this->no_habitacion = $habitacion->no_habitacion;
            $this->tipo = $habitacion->tipo;
            $this->precio = $habitacion->precio;
            $this->estado = $habitacion->estado;
            $this->planta = $habitacion->planta;
            $this->mostrarModal = true;
        }
    }

    public function guardar()
    {
        if ($this->editando_id) {
            $this->validate([
                'no_habitacion' => 'required|integer|unique:habitaciones,no_habitacion,' . $this->editando_id . ',idhabitacion',
                'tipo' => 'required|string|in:individual,doble,suite',
                'precio' => 'required|numeric|min:0',
                'estado' => 'required|string|in:disponible,ocupada,en_mantenimiento',
                'planta' => 'required|string|in:Planta 1,Planta 2,Planta 3',
            ]);

            try {
                DB::table('habitaciones')
                    ->where('idhabitacion', $this->editando_id)
                    ->update([
                        'no_habitacion' => $this->no_habitacion,
                        'tipo' => $this->tipo,
                        'precio' => $this->precio,
                        'estado' => $this->estado,
                        'planta' => $this->planta,
                    ]);

                session()->flash('message', 'Habitación actualizada exitosamente.');
                $this->cerrarModal();
            } catch (\Exception $e) {
                session()->flash('error', 'Error al actualizar: ' . $e->getMessage());
            }
        } else {
            $this->validate();

            try {
                DB::table('habitaciones')->insert([
                    'no_habitacion' => $this->no_habitacion,
                    'tipo' => $this->tipo,
                    'precio' => $this->precio,
                    'estado' => $this->estado,
                    'planta' => $this->planta,
                ]);

                session()->flash('message', 'Habitación creada exitosamente.');
                $this->cerrarModal();
            } catch (\Exception $e) {
                session()->flash('error', 'Error al crear: ' . $e->getMessage());
            }
        }
    }

    public function eliminar($habitacionId)
    {
        try {
            $tieneReservas = DB::table('habitaciones_has_reservas')
                ->join('reservas', 'habitaciones_has_reservas.reservas_idreservas', '=', 'reservas.idreservas')
                ->where('habitaciones_has_reservas.habitaciones_idhabitacion', $habitacionId)
                ->whereIn('reservas.estado', ['confirmada', 'pendiente'])
                ->exists();

            if ($tieneReservas) {
                session()->flash('error', 'No se puede eliminar. La habitación tiene reservas activas.');
                return;
            }

            DB::table('habitaciones')->where('idhabitacion', $habitacionId)->delete();
            session()->flash('message', 'Habitación eliminada exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->reset(['no_habitacion', 'tipo', 'precio', 'estado', 'planta', 'editando_id']);
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = DB::table('habitaciones')
            ->orderBy('planta')
            ->orderBy('no_habitacion');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('no_habitacion', 'like', '%' . $this->search . '%')
                  ->orWhere('tipo', 'like', '%' . $this->search . '%');
            });
        }

        $habitaciones = $query->paginate(15);

        return view('livewire.gerente.gestion-habitaciones', [
            'habitaciones' => $habitaciones
        ]);
    }
}
