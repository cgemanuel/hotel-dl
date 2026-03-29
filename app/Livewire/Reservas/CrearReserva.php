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

    // Autocomplete
    public $sugerencias_clientes = [];
    public $mostrar_sugerencias = false;

    // Datos de la reserva
    public $folio = '';
    public $fecha_reserva;
    public $fecha_check_in = '';
    public $fecha_check_out = '';
    public $no_personas = 1;

    public $habitaciones_ids = [];
    public $plataforma_id = '';

    // Método de pago
    public $metodo_pago = '';
    public $monto_efectivo = 0;
    public $monto_tarjeta = 0;           // genérico (débito por defecto en combinado)
    public $monto_tarjeta_debito = 0;    // ← nuevo campo específico
    public $monto_tarjeta_credito = 0;   // ← nuevo campo específico
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
            ->select('idclientes', 'nom_completo')
            ->get();

        $this->cargarHabitacionesDisponibles();

        $this->plataformas = DB::table('plat_reserva')->get();
    }

    public function cargarHabitacionesDisponibles()
    {
        $query = DB::table('habitaciones')
            ->whereNotIn('estado', ['en_mantenimiento', 'mantenimiento'])
            ->orderBy('no_habitacion');

        if ($this->fecha_check_in && $this->fecha_check_out) {
            $query->whereNotIn('idhabitacion', function ($sub) {
                $sub->select('habitaciones_idhabitacion')
                    ->from('habitaciones_has_reservas')
                    ->join('reservas', 'reservas_idreservas', '=', 'reservas.idreservas')
                    ->whereIn('reservas.estado', ['confirmada', 'pendiente', 'ocupada', 'check_in', 'hospedada'])
                    ->where('reservas.fecha_check_in', '<', $this->fecha_check_out)
                    ->where('reservas.fecha_check_out', '>', $this->fecha_check_in);
            });
        } elseif ($this->fecha_check_in) {
            $query->whereNotIn('idhabitacion', function ($sub) {
                $sub->select('habitaciones_idhabitacion')
                    ->from('habitaciones_has_reservas')
                    ->join('reservas', 'reservas_idreservas', '=', 'reservas.idreservas')
                    ->whereIn('reservas.estado', ['confirmada', 'pendiente', 'ocupada', 'check_in', 'hospedada'])
                    ->where('reservas.fecha_check_in', '<=', $this->fecha_check_in)
                    ->where('reservas.fecha_check_out', '>', $this->fecha_check_in);
            });
        }

        $this->habitaciones = $query->get();

        $idsDisponibles = collect($this->habitaciones)->pluck('idhabitacion')->map(fn($v) => (int)$v)->toArray();
        $this->habitaciones_ids = array_values(array_intersect(
            array_map('intval', $this->habitaciones_ids),
            $idsDisponibles
        ));
    }

    // ── Autocomplete: buscar clientes mientras el usuario escribe ──
    public function updatedNomCompleto($value)
    {
        if ($this->cliente_existente) return;

        if (strlen(trim($value)) >= 2) {
            $this->sugerencias_clientes = DB::table('clientes')
                ->where('nom_completo', 'like', '%' . trim($value) . '%')
                ->select('idclientes', 'nom_completo', 'tipo_identificacion', 'direccion', 'pais_origen')
                ->limit(8)
                ->get()
                ->toArray();
            $this->mostrar_sugerencias = count($this->sugerencias_clientes) > 0;
        } else {
            $this->sugerencias_clientes = [];
            $this->mostrar_sugerencias = false;
        }
    }

    // ── Seleccionar cliente desde el autocomplete ──
    public function seleccionarClienteAutocomplete($clienteId)
    {
        $cliente = DB::table('clientes')->where('idclientes', $clienteId)->first();
        if ($cliente) {
            $this->cliente_id        = $cliente->idclientes;
            $this->nom_completo      = $cliente->nom_completo;
            $this->tipo_identificacion = $cliente->tipo_identificacion;
            $this->direccion         = $cliente->direccion;
            $this->pais_origen       = $cliente->pais_origen;
            $this->cliente_existente = true;
        }
        $this->sugerencias_clientes = [];
        $this->mostrar_sugerencias  = false;
    }

    public function limpiarClienteSeleccionado()
    {
        $this->cliente_existente   = false;
        $this->cliente_id          = '';
        $this->nom_completo        = '';
        $this->tipo_identificacion = '';
        $this->direccion           = '';
        $this->pais_origen         = 'México';
        $this->sugerencias_clientes = [];
        $this->mostrar_sugerencias  = false;
    }

    public function updatedFechaCheckIn()
    {
        $this->cargarHabitacionesDisponibles();
    }

    public function updatedFechaCheckOut()
    {
        $this->cargarHabitacionesDisponibles();
    }

    public function abrir()
    {
        $this->mostrarModal = true;
        $this->reset([
            'nom_completo', 'tipo_identificacion', 'direccion',
            'cliente_id', 'fecha_check_in', 'fecha_check_out',
            'no_personas', 'habitaciones_ids', 'plataforma_id',
            'metodo_pago', 'monto_efectivo', 'monto_tarjeta',
            'monto_tarjeta_debito', 'monto_tarjeta_credito',
            'monto_transferencia', 'total_reserva', 'folio',
            'sugerencias_clientes', 'mostrar_sugerencias',
        ]);
        $this->cliente_existente = false;
        $this->pais_origen = 'México';
        $this->habitaciones_ids = [];
        $this->cargarDatos();
    }

    public function cerrar()
    {
        $this->mostrarModal = false;
        $this->sugerencias_clientes = [];
        $this->mostrar_sugerencias  = false;
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
                $this->cliente_existente   = true;
            }
        } else {
            $this->cliente_existente = false;
            $this->reset(['nom_completo', 'tipo_identificacion', 'direccion']);
            $this->pais_origen = 'México';
        }
    }

    public function guardar()
    {
        $this->validate([
            'folio'               => 'required|string|max:50|unique:reservas,folio',
            'nom_completo'        => 'required|min:3',
            'tipo_identificacion' => 'required',
            'direccion'           => 'required',
            'pais_origen'         => 'required',
            'fecha_check_in'      => 'required|date',
            'fecha_check_out'     => 'required|date|after:fecha_check_in',
            'no_personas'         => 'required|integer|min:1',
            'habitaciones_ids'    => 'required|array|min:1',
            'habitaciones_ids.*'  => 'exists:habitaciones,idhabitacion',
            'plataforma_id'       => 'required|exists:plat_reserva,idplat_reserva',
            'metodo_pago'         => 'required|in:efectivo,tarjeta_debito,tarjeta_credito,transferencia,combinado,cortesia',
            'total_reserva'       => 'required|numeric|min:0',
        ], [
            'folio.required'               => 'El folio es obligatorio',
            'folio.unique'                 => 'Este folio ya existe',
            'nom_completo.required'        => 'El nombre completo es obligatorio',
            'tipo_identificacion.required' => 'Seleccione un tipo de identificación',
            'direccion.required'           => 'La dirección es obligatoria',
            'pais_origen.required'         => 'El país es obligatorio',
            'fecha_check_out.after'        => 'La fecha de check-out debe ser posterior al check-in',
            'habitaciones_ids.required'    => 'Debe seleccionar al menos una habitación',
            'habitaciones_ids.min'         => 'Debe seleccionar al menos una habitación',
            'metodo_pago.required'         => 'Debe seleccionar un método de pago',
            'metodo_pago.in'               => 'Método de pago no válido',
            'total_reserva.required'       => 'Debe ingresar el total de la reserva',
            'total_reserva.min'            => 'El total debe ser mayor o igual a 0',
        ]);

        // Validación de montos en pago combinado
        if ($this->metodo_pago === 'combinado') {
            $totalCombinado = floatval($this->monto_efectivo)
                + floatval($this->monto_tarjeta_debito)
                + floatval($this->monto_tarjeta_credito)
                + floatval($this->monto_transferencia);

            if ($totalCombinado <= 0) {
                session()->flash('error', 'Debe ingresar al menos un monto en el pago combinado.');
                return;
            }

            // Auto-calcular total si no fue ingresado
            if (floatval($this->total_reserva) <= 0) {
                $this->total_reserva = $totalCombinado;
            }
        }

        try {
            DB::beginTransaction();

            // ── Crear o reusar cliente ──
            if (!$this->cliente_existente || !$this->cliente_id) {
                $this->cliente_id = DB::table('clientes')->insertGetId([
                    'nom_completo'        => $this->nom_completo,
                    'tipo_identificacion' => $this->tipo_identificacion,
                    'no_identificacion'   => '',
                    'direccion'           => $this->direccion,
                    'edad'                => 0,
                    'estado_origen'       => '',
                    'pais_origen'         => $this->pais_origen,
                    'telefono'            => '',
                    'correo'              => '',
                ]);
            }

            // Determinar montos para guardar en BD
            // monto_tarjeta = débito + crédito (compatibilidad columna existente)
            $montoTarjetaTotal = floatval($this->monto_tarjeta_debito) + floatval($this->monto_tarjeta_credito);

            // ── Crear reserva ──
            $reserva_id = DB::table('reservas')->insertGetId([
                'folio'                       => $this->folio,
                'fecha_reserva'               => $this->fecha_reserva,
                'fecha_check_in'              => $this->fecha_check_in,
                'fecha_check_out'             => $this->fecha_check_out,
                'no_personas'                 => $this->no_personas,
                'estado'                      => 'confirmada',
                'metodo_pago'                 => $this->metodo_pago,
                'monto_efectivo'              => floatval($this->monto_efectivo ?? 0),
                'monto_tarjeta'               => $montoTarjetaTotal,
                'monto_transferencia'         => floatval($this->monto_transferencia ?? 0),
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
                'folio'              => $this->folio,
                'cliente_id'        => $this->cliente_id,
                'habitaciones_ids'  => $this->habitaciones_ids,
                'fecha_check_in'    => $this->fecha_check_in,
                'fecha_check_out'   => $this->fecha_check_out,
                'estado'            => 'confirmada',
                'total_reserva'     => $this->total_reserva,
                'metodo_pago'       => $this->metodo_pago,
                'monto_efectivo'    => $this->monto_efectivo,
                'monto_tarjeta_debito'  => $this->monto_tarjeta_debito,
                'monto_tarjeta_credito' => $this->monto_tarjeta_credito,
                'monto_transferencia'   => $this->monto_transferencia,
            ]);

            // ── Vincular habitaciones ──
            $hoy = now()->toDateString();

            foreach ($this->habitaciones_ids as $habitacion_id) {
                DB::table('habitaciones_has_reservas')->insert([
                    'habitaciones_idhabitacion' => $habitacion_id,
                    'reservas_idreservas'       => $reserva_id,
                ]);

                if ($this->fecha_check_in <= $hoy) {
                    DB::table('habitaciones')
                        ->where('idhabitacion', $habitacion_id)
                        ->update(['estado' => 'ocupada']);
                }
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
