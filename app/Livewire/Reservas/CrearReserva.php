<?php

namespace App\Livewire\Reservas;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class CrearReserva extends Component
{
    public $mostrarModal = false;

    // Datos del cliente
    public $cliente_id = '';
    public $nom_completo = '';
    public $tipo_identificacion = '';
    public $no_identificacion = '';
    public $direccion = '';
    public $edad = '';
    public $estado_origen = '';
    public $pais_origen = 'México';
    public $telefono = '';
    public $correo = '';

    // Datos de la reserva
    public $fecha_reserva;
    public $fecha_check_in = '';
    public $fecha_check_out = '';
    public $no_personas = 1;
    public $habitacion_id = '';
    public $necesita_estacionamiento = false; // Por defecto No
    public $espacio_estacionamiento = '';
    public $plataforma_id = '';

    public $clientes = [];
    public $habitaciones = [];
    public $espacios_estacionamiento = [];
    public $plataformas = [];
    public $cliente_existente = false;

    protected $listeners = ['abrirModal' => 'abrir'];

    public function mount()
    {
        $this->fecha_reserva = now()->format('Y-m-d');
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->clientes = DB::table('clientes')
            ->select('idclientes', 'nom_completo', 'telefono')
            ->get();

        $this->habitaciones = DB::table('habitaciones')
            ->where('estado', 'disponible')
            ->get();

        $this->plataformas = DB::table('plat_reserva')->get();

        // Solo cargar estacionamientos si necesita
        if ($this->necesita_estacionamiento) {
            $this->cargarEstacionamientos();
        }
    }

    // Método para cargar estacionamiento
    public function cargarEstacionamientos()
    {
        $this->espacios_estacionamiento = DB::table('estacionamiento')
            ->where('estado', 'disponible')
            ->get();
    }

    // Se ejecuta cuando cambia necesita_estacionamiento
    public function updatedNecesitaEstacionamiento($value)
    {
        if ($value) {
            $this->cargarEstacionamientos();
        } else {
            // Si selecciona "No", limpiar el espacio seleccionado
            $this->espacio_estacionamiento = '';
            $this->espacios_estacionamiento = [];
        }
    }

    public function abrir()
    {
        $this->mostrarModal = true;
        $this->reset([
            'nom_completo', 'tipo_identificacion', 'no_identificacion',
            'direccion', 'edad', 'estado_origen', 'telefono', 'correo',
            'fecha_check_in', 'fecha_check_out', 'no_personas',
            'habitacion_id', 'necesita_estacionamiento', 'espacio_estacionamiento', 'plataforma_id'
        ]);
        $this->cliente_existente = false;
        $this->cargarDatos();
    }

    public function cerrar()
    {
        $this->mostrarModal = false;
    }

    public function seleccionarCliente()
    {
        if ($this->cliente_id) {
            $cliente = DB::table('clientes')
                ->where('idclientes', $this->cliente_id)
                ->first();

            if ($cliente) {
                $this->nom_completo = $cliente->nom_completo;
                $this->tipo_identificacion = $cliente->tipo_identificacion;
                $this->no_identificacion = $cliente->no_identificacion;
                $this->direccion = $cliente->direccion;
                $this->edad = $cliente->edad;
                $this->estado_origen = $cliente->estado_origen;
                $this->pais_origen = $cliente->pais_origen;
                $this->telefono = $cliente->telefono;
                $this->correo = $cliente->correo;
                $this->cliente_existente = true;
            }
        } else {
            $this->cliente_existente = false;
            $this->reset([
                'nom_completo', 'tipo_identificacion', 'no_identificacion',
                'direccion', 'edad', 'estado_origen', 'telefono', 'correo'
            ]);
        }
    }

    public function guardar()
    {
        // Validaciones con estacionamiento opcional
        $this->validate([
            'nom_completo' => 'required|min:3',
            'tipo_identificacion' => 'required',
            'no_identificacion' => 'required',
            'direccion' => 'required',
            'edad' => 'required|integer|min:18',
            'estado_origen' => 'required',
            'pais_origen' => 'required',
            'telefono' => 'required|min:10',
            'correo' => 'required|email',
            'fecha_check_in' => 'required|date|after_or_equal:today',
            'fecha_check_out' => 'required|date|after:fecha_check_in',
            'no_personas' => 'required|integer|min:1',
            'habitacion_id' => 'required|exists:habitaciones,idhabitacion',
            'necesita_estacionamiento' => 'boolean',
            'espacio_estacionamiento' => 'nullable|required_if:necesita_estacionamiento,1|exists:estacionamiento,no_espacio',
            'plataforma_id' => 'required|exists:plat_reserva,idplat_reserva',
        ], [
            'nom_completo.required' => 'El nombre completo es obligatorio',
            'tipo_identificacion.required' => 'Seleccione un tipo de identificación',
            'fecha_check_in.after_or_equal' => 'La fecha de check-in no puede ser anterior a hoy',
            'fecha_check_out.after' => 'La fecha de check-out debe ser posterior al check-in',
            'edad.min' => 'El cliente debe ser mayor de edad',
            'espacio_estacionamiento.required_if' => 'Debe seleccionar un espacio de estacionamiento',
        ]);

        try {
            DB::beginTransaction();

            // Crear o usar cliente existente
            if (!$this->cliente_existente) {
                $this->cliente_id = DB::table('clientes')->insertGetId([
                    'nom_completo' => $this->nom_completo,
                    'tipo_identificacion' => $this->tipo_identificacion,
                    'no_identificacion' => $this->no_identificacion,
                    'direccion' => $this->direccion,
                    'edad' => $this->edad,
                    'estado_origen' => $this->estado_origen,
                    'pais_origen' => $this->pais_origen,
                    'telefono' => $this->telefono,
                    'correo' => $this->correo,
                ]);
            }

            // Crear reserva - estacionamiento puede ser null
            $reserva_id = DB::table('reservas')->insertGetId([
                'fecha_reserva' => $this->fecha_reserva,
                'fecha_check_in' => $this->fecha_check_in,
                'fecha_check_out' => $this->fecha_check_out,
                'no_personas' => $this->no_personas,
                'estado' => 'confirmada',
                'clientes_idclientes' => $this->cliente_id,
                'estacionamiento_no_espacio' => $this->necesita_estacionamiento ? $this->espacio_estacionamiento : null,
                'plat_reserva_idplat_reserva' => $this->plataforma_id,
            ]);

            // Relacionar habitación con reserva
            DB::table('habitaciones_has_reservas')->insert([
                'habitaciones_idhabitacion' => $this->habitacion_id,
                'reservas_idreservas' => $reserva_id,
            ]);

            // Actualizar estado de habitación
            DB::table('habitaciones')
                ->where('idhabitacion', $this->habitacion_id)
                ->update(['estado' => 'ocupada']);

            // Solo actualizar estacionamiento si fue seleccionado
            if ($this->necesita_estacionamiento && $this->espacio_estacionamiento) {
                DB::table('estacionamiento')
                    ->where('no_espacio', $this->espacio_estacionamiento)
                    ->update(['estado' => 'ocupado']);
            }

            DB::commit();

            session()->flash('message', 'Reserva creada exitosamente.');
            $this->cerrar();
            $this->dispatch('reserva-creada');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al crear la reserva: ' . $e->getMessage());
        }
    }



    public function render()
    {
        return view('livewire.reservas.crear-reserva');
    }
}
