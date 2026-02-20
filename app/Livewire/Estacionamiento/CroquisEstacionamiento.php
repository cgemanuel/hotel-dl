<?php

namespace App\Livewire\Estacionamiento;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class CroquisEstacionamiento extends Component
{
    public $espacios = [];
    public $espacioSeleccionado = null;
    public $mostrarModal = false;

    protected $listeners = ['reserva-actualizada' => '$refresh', 'espacioReservado' => '$refresh', 'espacio-estado-cambiado' => 'cargarEspacios'];

    public function mount()
    {
        $this->cargarEspacios();
    }

    public function cargarEspacios()
    {
        $this->espacios = DB::table('estacionamiento')
            ->leftJoin('reservas', function($join) {
                $join->on('estacionamiento.no_espacio', '=', 'reservas.estacionamiento_no_espacio')
                    ->where('reservas.estado', '=', 'confirmada')
                    ->where('reservas.fecha_check_out', '>=', now()->toDateString());
            })
            ->leftJoin('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
            ->select(
                'estacionamiento.no_espacio as numero',
                'estacionamiento.estado',
                'clientes.nom_completo',
                'reservas.fecha_check_in',
                'reservas.fecha_check_out'
            )
            ->orderBy('estacionamiento.no_espacio')
            ->get()
            ->toArray();
    }

    public function seleccionarEspacio($numero)
    {
        $espacio = collect($this->espacios)->firstWhere('numero', $numero);
        $this->espacioSeleccionado = (array) $espacio;

        // Obtener habitaciones vinculadas a la reserva de este espacio
        $reserva = DB::table('reservas')
            ->where('estacionamiento_no_espacio', $numero)
            ->where('estado', 'confirmada')
            ->first();

        if ($reserva) {
            $habitaciones = DB::table('habitaciones_has_reservas')
                ->join('habitaciones', 'habitaciones.idhabitacion', '=', 'habitaciones_has_reservas.habitaciones_idhabitacion')
                ->where('habitaciones_has_reservas.reservas_idreservas', $reserva->idreservas)
                ->select('habitaciones.no_habitacion', 'habitaciones.tipo')
                ->get();

            // Convertir a array asociativo puro para que Livewire pueda serializarlo correctamente
            $this->espacioSeleccionado['habitaciones'] = $habitaciones->map(fn($h) => [
                'no_habitacion' => $h->no_habitacion,
                'tipo'          => $h->tipo,
            ])->toArray();
        } else {
            $this->espacioSeleccionado['habitaciones'] = [];
        }

        $this->mostrarModal = true;
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->espacioSeleccionado = null;
    }

    public function cambiarEstadoEspacio($numero, $nuevoEstado)
    {
        try {
            $espacio = DB::table('estacionamiento')
                ->where('no_espacio', $numero)
                ->first();

            \App\Services\AuditService::logUpdated(
                'Estacionamiento',
                $numero,
                ['estado' => $espacio->estado],
                ['estado' => $nuevoEstado]
            );

            DB::table('estacionamiento')
                ->where('no_espacio', $numero)
                ->update([
                    'estado' => $nuevoEstado,
                    'updated_at' => now()
                ]);

            session()->flash('message', 'Estado del espacio de estacionamiento actualizado exitosamente.');

            $this->cargarEspacios();
            $this->cerrarModal();

            $this->dispatch('espacio-estado-cambiado');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cambiar el estado: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $mitad = ceil(count($this->espacios) / 2);
        $espaciosIzquierda = array_slice($this->espacios, 0, $mitad);
        $espaciosDerecha = array_slice($this->espacios, $mitad);

        return view('livewire.estacionamiento.croquis-estacionamiento', [
            'espaciosIzquierda' => $espaciosIzquierda,
            'espaciosDerecha' => $espaciosDerecha,
        ]);
    }
}
