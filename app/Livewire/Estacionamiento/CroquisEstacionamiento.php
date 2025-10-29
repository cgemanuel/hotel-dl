<?php

namespace App\Livewire\Estacionamiento;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class CroquisEstacionamiento extends Component
{
    public $espacios = [];
    public $espacioSeleccionado = null;
    public $mostrarModal = false;

    protected $listeners = ['reserva-actualizada' => '$refresh', 'espacioReservado' => '$refresh'];

    public function mount()
    {
        $this->cargarEspacios();
    }

    public function cargarEspacios()
    {
        $this->espacios = DB::table('estacionamiento')
            ->leftJoin('reservas', 'estacionamiento.no_espacio', '=', 'reservas.estacionamiento_no_espacio')
            ->leftJoin('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
            ->select(
                'estacionamiento.no_espacio as numero',
                'estacionamiento.estado',
                'clientes.nom_completo',
                'clientes.telefono',
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
        $this->mostrarModal = true;
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->espacioSeleccionado = null;
    }

    public function getColorEstadoProperty()
    {
        $colores = [
            'disponible' => 'bg-green-500 hover:bg-green-600',
            'ocupado' => 'bg-red-500 hover:bg-red-600',
            'mantenimiento' => 'bg-yellow-500 hover:bg-yellow-600',
        ];

        return $colores[$this->espacioSeleccionado['estado'] ?? 'disponible'] ?? 'bg-gray-500';
    }

    public function render()
    {
        // Separar espacios en dos grupos (izquierda y derecha)
        $mitad = ceil(count($this->espacios) / 2);
        $espaciosIzquierda = array_slice($this->espacios, 0, $mitad);
        $espaciosDerecha = array_slice($this->espacios, $mitad);

        return view('livewire.estacionamiento.croquis-estacionamiento', [
            'espaciosIzquierda' => $espaciosIzquierda,
            'espaciosDerecha' => $espaciosDerecha,
        ]);
    }
}
