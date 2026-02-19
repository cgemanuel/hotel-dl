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
    public $direccion = '';
    public $pais_origen = 'México';
    public $correo = '';

    // Datos de la reserva
    public $folio = '';
    public $fecha_reserva;
    public $fecha_check_in = '';
    public $fecha_check_out = '';
    public $no_personas = 1;

    // ── CAMBIO: de una sola habitacion a un array ──
    public $habitaciones_ids = [];   // array de idhabitacion seleccionados

    public $plataforma_id = '';

    // Método de pago
    public $metodo_pago = '';
    public $monto_efectivo = 0;
    public $monto_tarjeta = 0;
    public $monto_transferencia = 0;

    public $total_reserva = 0;
    public $clientes = [];
    public $habitaciones = [];
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
            ->select('idclientes', 'nom_completo', 'correo')
            ->get();

        $this->habitaciones = DB::table('habitaciones')
            ->where('estado', 'disponible')
            ->orderBy('no_habitacion')
            ->get();

        $this->plataformas = DB::table('plat_reserva')->get();
    }

    public function abrir()
    {
        $this->mostrarModal = true;
        $this->reset([
            'nom_completo', 'tipo_identificacion',
            'direccion', 'correo',
            'fecha_check_in', 'fecha_check_out', 'no_personas',
            'habitaciones_ids', 'plataforma_id', 'metodo_pago',
            'monto_efectivo', 'monto_tarjeta', 'monto_transferencia',
            'total_reserva', 'folio'
        ]);
        $this->cliente_existente = false;
        $this->pais_origen = 'México';
        $this->habitaciones_ids = [];
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
                $this->nom_completo        = $cliente->nom_completo;
                $this->tipo_identificacion = $cliente->tipo_identificacion;
                $this->direccion           = $cliente->direccion;
                $this->pais_origen         = $cliente->pais_origen;
                $this->correo              = $cliente->correo;
                $this->cliente_existente   = true;
            }
        } else {
            $this->cliente_existente = false;
            $this->reset(['nom_completo', 'tipo_identificacion', 'direccion', 'correo']);
            $this->pais_origen = 'México';
        }
    }

    // ── Helper: devuelve las habitaciones seleccionadas como colección ──
    public function getHabitacionesSeleccionadasProperty()
    {
        if (empty($this->habitaciones_ids)) {
            return collect();
        }
        return collect($this->habitaciones)->filter(
            fn($h) => in_array($h->idhabitacion, $this->habitaciones_ids)
        );
    }

    public function guardar()
    {
        $this->validate([
            'folio'               => 'required|string|max:50|unique:reservas,folio',
            'nom_completo'        => 'required|min:3',
            'tipo_identificacion' => 'required',
            'direccion'           => 'required',
            'pais_origen'         => 'required',
            'correo'              => 'required|email',
            'fecha_check_in'      => 'required|date|after_or_equal:today',
            'fecha_check_out'     => 'required|date|after:fecha_check_in',
            'no_personas'         => 'required|integer|min:1',
            // ── CAMBIO: ahora validamos el array ──
            'habitaciones_ids'    => 'required|array|min:1',
            'habitaciones_ids.*'  => 'exists:habitaciones,idhabitacion',
            'plataforma_id'       => 'required|exists:plat_reserva,idplat_reserva',
            'metodo_pago'         => 'required|in:efectivo,tarjeta_debito,tarjeta_credito,transferencia,combinado',
            'total_reserva'       => 'required|numeric|min:0',
        ], [
            'folio.required'               => 'El folio es obligatorio',
            'folio.unique'                 => 'Este folio ya existe',
            'nom_completo.required'        => 'El nombre completo es obligatorio',
            'tipo_identificacion.required' => 'Seleccione un tipo de identificación',
            'fecha_check_in.after_or_equal'=> 'La fecha de check-in no puede ser anterior a hoy',
            'fecha_check_out.after'        => 'La fecha de check-out debe ser posterior al check-in',
            'habitaciones_ids.required'    => 'Debe seleccionar al menos una habitación',
            'habitaciones_ids.min'         => 'Debe seleccionar al menos una habitación',
            'metodo_pago.required'         => 'Debe seleccionar un método de pago',
            'metodo_pago.in'               => 'Método de pago no válido',
            'total_reserva.required'       => 'Debe ingresar el total de la reserva',
            'total_reserva.min'            => 'El total debe ser mayor a 0',
        ]);

        if ($this->metodo_pago === 'combinado') {
            $total = $this->monto_efectivo + $this->monto_tarjeta + $this->monto_transferencia;
            if ($total <= 0) {
                session()->flash('error', 'Debe ingresar al menos un monto en el pago combinado.');
                return;
            }
        }

        try {
            DB::beginTransaction();

            // ── Crear o reusar cliente ──
            if (!$this->cliente_existente) {
                $this->cliente_id = DB::table('clientes')->insertGetId([
                    'nom_completo'        => $this->nom_completo,
                    'tipo_identificacion' => $this->tipo_identificacion,
                    'no_identificacion'   => null,
                    'direccion'           => $this->direccion,
                    'edad'                => null,
                    'estado_origen'       => null,
                    'pais_origen'         => $this->pais_origen,
                    'telefono'            => '0000000000',
                    'correo'              => $this->correo,
                ]);
            }

            // ── Crear reserva ──
            $reserva_id = DB::table('reservas')->insertGetId([
                'folio'                       => $this->folio,
                'fecha_reserva'               => $this->fecha_reserva,
                'fecha_check_in'              => $this->fecha_check_in,
                'fecha_check_out'             => $this->fecha_check_out,
                'no_personas'                 => $this->no_personas,
                'estado'                      => 'confirmada',
                'metodo_pago'                 => $this->metodo_pago,
                'monto_efectivo'              => $this->monto_efectivo ?? 0,
                'monto_tarjeta'               => $this->monto_tarjeta ?? 0,
                'monto_transferencia'         => $this->monto_transferencia ?? 0,
                'total_reserva'               => $this->total_reserva,
                'clientes_idclientes'         => $this->cliente_id,
                'estacionamiento_no_espacio'  => null,
                'tipo_vehiculo'               => null,
                'descripcion_vehiculo'        => null,
                'plat_reserva_idplat_reserva' => $this->plataforma_id,
                'created_by'                  => auth()->id(),
                'created_at'                  => now(),
                'updated_at'                  => now(),
            ]);

            // ── Auditoría ──
            \App\Services\AuditService::logCreated('Reserva', $reserva_id, [
                'folio'            => $this->folio,
                'cliente_id'       => $this->cliente_id,
                'habitaciones_ids' => $this->habitaciones_ids,
                'fecha_check_in'   => $this->fecha_check_in,
                'fecha_check_out'  => $this->fecha_check_out,
                'estado'           => 'confirmada',
                'total_reserva'    => $this->total_reserva,
            ]);

            // ── CAMBIO: vincular TODAS las habitaciones seleccionadas ──
            foreach ($this->habitaciones_ids as $habitacion_id) {
                DB::table('habitaciones_has_reservas')->insert([
                    'habitaciones_idhabitacion' => $habitacion_id,
                    'reservas_idreservas'       => $reserva_id,
                ]);

                DB::table('habitaciones')
                    ->where('idhabitacion', $habitacion_id)
                    ->update(['estado' => 'ocupada']);
            }

            DB::commit();

            $totalHabs = count($this->habitaciones_ids);
            $msg = $totalHabs === 1
                ? "Reserva creada exitosamente. Folio: {$this->folio}"
                : "Reserva creada exitosamente con {$totalHabs} habitaciones. Folio: {$this->folio}";

            session()->flash('message', $msg);
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