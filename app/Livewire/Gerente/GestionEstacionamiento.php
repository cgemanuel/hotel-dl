<?php

namespace App\Livewire\Gerente;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class GestionEstacionamiento extends Component
{
    public $espacios = [];
    public $mostrarModal = false;
    public $no_espacio;

    public function mount()
    {
        $this->cargarEspacios();
    }

    public function cargarEspacios()
    {
        $this->espacios = DB::table('estacionamiento')
            ->orderBy('no_espacio')
            ->get();
    }

    public function abrirModal()
    {
        $this->reset('no_espacio');
        $this->mostrarModal = true;
    }

    public function guardar()
    {
        $this->validate([
            'no_espacio' => 'required|integer|unique:estacionamiento,no_espacio',
        ], [
            'no_espacio.required' => 'El número de espacio es obligatorio',
            'no_espacio.unique' => 'Este número de espacio ya existe',
        ]);

        try {
            DB::table('estacionamiento')->insert([
                'no_espacio' => $this->no_espacio,
                'estado' => 'disponible',
            ]);

            // 🔥 AUDITORÍA: Registrar creación
            \App\Services\AuditService::logCreated(
                'Estacionamiento',
                $this->no_espacio,
                [
                    'no_espacio' => $this->no_espacio,
                    'estado' => 'disponible',
                ]
            );

            session()->flash('message', 'Espacio de estacionamiento creado exitosamente.');
            $this->cerrarModal();
            $this->cargarEspacios();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear: ' . $e->getMessage());
        }
    }

    public function eliminar($noEspacio)
    {
        try {
            $estaAsignado = DB::table('reservas')
                ->where('estacionamiento_no_espacio', $noEspacio)
                ->whereIn('estado', ['confirmada', 'pendiente'])
                ->exists();

            if ($estaAsignado) {
                session()->flash('error', 'No se puede eliminar. El espacio tiene reservas activas.');
                return;
            }

            // AUDITORÍA: Registrar eliminación
            \App\Services\AuditService::logDeleted(
                'Estacionamiento',
                $noEspacio,
                [
                    'no_espacio' => $noEspacio,
                    'estado' => 'disponible',
                ]
            );

            DB::table('estacionamiento')->where('no_espacio', $noEspacio)->delete();
            session()->flash('message', 'Espacio eliminado exitosamente.');
            $this->cargarEspacios();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->reset('no_espacio');
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.gerente.gestion-estacionamiento');
    }
}
