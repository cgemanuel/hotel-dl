<?php

namespace App\Livewire\Reservas;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\AuditService;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $estado_filtro = '';

    public $mostrarModalVer = false;
    public $mostrarModalEditar = false;
    public $mostrarModalEstacionamiento = false;
    public $reservaSeleccionada = null;

    public $reserva_para_estacionamiento = null;
    public $espacio_seleccionado = '';
    public $espacios_disponibles = [];

    public $tipo_vehiculo_temp = '';
    public $descripcion_vehiculo_temp = '';
    public $mostrar_form_vehiculo = false;

    public $editando_id = null;
    public $edit_folio = '';          // ← NUEVO: folio editable
    public $edit_fecha_reserva = '';
    public $edit_fecha_check_in = '';
    public $edit_fecha_check_out = '';
    public $edit_no_personas = 1;
    public $edit_estado = '';
    public $edit_estacionamiento_no_espacio = '';
    public $edit_total_reserva = 0;
    public $edit_tipo_vehiculo = '';
    public $edit_descripcion_vehiculo = '';

    // Datos del cliente (editables)
    public $edit_nom_completo        = '';
    public $edit_tipo_identificacion = '';
    public $edit_direccion           = '';
    public $edit_pais_origen         = '';

    // Plataforma
    public $edit_plataforma_id = '';
    public $plataformas        = [];

    public $edit_metodo_pago = '';

    // ── Habitaciones multi-selección ──
    public $edit_habitaciones_ids = [];
    public $edit_habitaciones_disponibles = [];

    protected $queryString = ['search', 'fecha_inicio', 'fecha_fin', 'estado_filtro'];
    protected $listeners = [
        'reserva-creada'      => '$refresh',
        'reserva-eliminada'   => '$refresh',
        'reserva-actualizada' => '$refresh',
    ];

    public function updatingSearch()       { $this->resetPage(); }
    public function updatingFechaInicio()  { $this->resetPage(); }
    public function updatingFechaFin()     { $this->resetPage(); }
    public function updatingEstadoFiltro() { $this->resetPage(); }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'fecha_inicio', 'fecha_fin', 'estado_filtro']);
        $this->resetPage();
    }

    // ══════════════════════════════════════════════════════════
    // ESTACIONAMIENTO
    // ══════════════════════════════════════════════════════════

    public function asignarEstacionamiento($reservaId)
    {
        try {
            $this->reserva_para_estacionamiento = $reservaId;
            $reserva = DB::table('reservas')->where('idreservas', $reservaId)->first();

            if (!$reserva) {
                session()->flash('error', 'Reserva no encontrada.');
                return;
            }

            $todosEspacios = DB::table('estacionamiento')->get();
            $this->espacios_disponibles = $todosEspacios->filter(function ($espacio) use ($reserva) {
                return $espacio->estado === 'disponible' ||
                       $espacio->no_espacio == $reserva->estacionamiento_no_espacio;
            })->values();

            $this->espacio_seleccionado         = $reserva->estacionamiento_no_espacio ?? '';
            $this->tipo_vehiculo_temp           = $reserva->tipo_vehiculo ?? '';
            $this->descripcion_vehiculo_temp    = $reserva->descripcion_vehiculo ?? '';
            $this->mostrar_form_vehiculo        = !empty($this->espacio_seleccionado);
            $this->mostrarModalEstacionamiento  = true;

        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar estacionamiento: ' . $e->getMessage());
        }
    }

    public function updatedEspacioSeleccionado($value)
    {
        $this->mostrar_form_vehiculo = !empty($value);
        if (empty($value)) {
            $this->tipo_vehiculo_temp        = '';
            $this->descripcion_vehiculo_temp = '';
        }
    }

    public function guardarEstacionamiento()
    {
        if ($this->espacio_seleccionado) {
            $this->validate([
                'espacio_seleccionado'      => 'required',
                'tipo_vehiculo_temp'        => 'required|string|max:100',
                'descripcion_vehiculo_temp' => 'nullable|string|max:500',
            ], [
                'tipo_vehiculo_temp.required' => 'El tipo de vehículo es obligatorio al asignar estacionamiento',
            ]);
        }

        try {
            DB::beginTransaction();

            $reserva = DB::table('reservas')
                ->where('idreservas', $this->reserva_para_estacionamiento)
                ->first();

            if (!$reserva) throw new \Exception('Reserva no encontrada');

            AuditService::logUpdated('Reserva', $this->reserva_para_estacionamiento,
                [
                    'estacionamiento_no_espacio' => $reserva->estacionamiento_no_espacio,
                    'tipo_vehiculo'              => $reserva->tipo_vehiculo,
                    'descripcion_vehiculo'       => $reserva->descripcion_vehiculo,
                ],
                [
                    'estacionamiento_no_espacio' => $this->espacio_seleccionado ?: null,
                    'tipo_vehiculo'              => $this->tipo_vehiculo_temp ?: null,
                    'descripcion_vehiculo'       => $this->descripcion_vehiculo_temp ?: null,
                ]
            );

            if ($reserva->estacionamiento_no_espacio &&
                $reserva->estacionamiento_no_espacio != $this->espacio_seleccionado) {
                DB::table('estacionamiento')
                    ->where('no_espacio', $reserva->estacionamiento_no_espacio)
                    ->update(['estado' => 'disponible']);
            }

            if (!$this->espacio_seleccionado && $reserva->estacionamiento_no_espacio) {
                DB::table('estacionamiento')
                    ->where('no_espacio', $reserva->estacionamiento_no_espacio)
                    ->update(['estado' => 'disponible']);
            }

            if ($this->espacio_seleccionado &&
                $this->espacio_seleccionado != $reserva->estacionamiento_no_espacio) {
                DB::table('estacionamiento')
                    ->where('no_espacio', $this->espacio_seleccionado)
                    ->update(['estado' => 'ocupado']);
            }

            DB::table('reservas')
                ->where('idreservas', $this->reserva_para_estacionamiento)
                ->update([
                    'estacionamiento_no_espacio' => $this->espacio_seleccionado ?: null,
                    'tipo_vehiculo'              => $this->tipo_vehiculo_temp ?: null,
                    'descripcion_vehiculo'       => $this->descripcion_vehiculo_temp ?: null,
                    'updated_at'                 => now(),
                ]);

            DB::commit();
            session()->flash('message', 'Estacionamiento actualizado exitosamente.');
            $this->cerrarModalEstacionamiento();
            $this->dispatch('reserva-actualizada');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al asignar estacionamiento: ' . $e->getMessage());
        }
    }

    public function cerrarModalEstacionamiento()
    {
        $this->mostrarModalEstacionamiento   = false;
        $this->reserva_para_estacionamiento  = null;
        $this->espacio_seleccionado          = '';
        $this->espacios_disponibles          = [];
        $this->tipo_vehiculo_temp            = '';
        $this->descripcion_vehiculo_temp     = '';
        $this->mostrar_form_vehiculo         = false;
    }

    // ══════════════════════════════════════════════════════════
    // VER
    // ══════════════════════════════════════════════════════════

    public function ver($id)
    {
        try {
            $reserva = DB::table('reservas')
                ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
                ->leftJoin('plat_reserva', 'reservas.plat_reserva_idplat_reserva', '=', 'plat_reserva.idplat_reserva')
                ->leftJoin('estacionamiento', 'reservas.estacionamiento_no_espacio', '=', 'estacionamiento.no_espacio')
                ->where('reservas.idreservas', $id)
                ->select(
                    'reservas.*',
                    'clientes.nom_completo',
                    'clientes.tipo_identificacion',
                    'clientes.direccion',
                    'clientes.pais_origen',
                    'plat_reserva.nombre_plataforma',
                    'plat_reserva.comision',
                    'estacionamiento.no_espacio'
                )
                ->first();

            if ($reserva) {
                $this->reservaSeleccionada = (array) $reserva;
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
        $this->mostrarModalVer     = false;
        $this->reservaSeleccionada = null;
    }

    // ══════════════════════════════════════════════════════════
    // EDITAR
    // ══════════════════════════════════════════════════════════

    public function editar($id)
    {
        $reserva = DB::table('reservas')
            ->join('clientes', 'clientes.idclientes', '=', 'reservas.clientes_idclientes')
            ->leftJoin('plat_reserva', 'plat_reserva.idplat_reserva', '=', 'reservas.plat_reserva_idplat_reserva')
            ->where('reservas.idreservas', $id)
            ->select(
                'reservas.*',
                'clientes.nom_completo',
                'clientes.tipo_identificacion',
                'clientes.direccion',
                'clientes.pais_origen',
                'plat_reserva.nombre_plataforma'
            )
            ->first();

        if (!$reserva) {
            session()->flash('error', 'Reserva no encontrada.');
            return;
        }

        $this->editando_id                      = $reserva->idreservas;
        $this->edit_folio                       = $reserva->folio;           // ← NUEVO
        $this->edit_fecha_reserva               = $reserva->fecha_reserva;
        $this->edit_fecha_check_in              = $reserva->fecha_check_in;
        $this->edit_fecha_check_out             = $reserva->fecha_check_out;
        $this->edit_no_personas                 = $reserva->no_personas;
        $this->edit_estado                      = $reserva->estado;
        $this->edit_nom_completo                = $reserva->nom_completo;
        $this->edit_tipo_identificacion         = $reserva->tipo_identificacion ?? '';
        $this->edit_direccion                   = $reserva->direccion ?? '';
        $this->edit_pais_origen                 = $reserva->pais_origen ?? '';
        $this->edit_plataforma_id               = $reserva->plat_reserva_idplat_reserva ?? '';
        $this->edit_metodo_pago                 = $reserva->metodo_pago ?? '';
        $this->edit_total_reserva               = $reserva->total_reserva;
        $this->edit_estacionamiento_no_espacio  = $reserva->estacionamiento_no_espacio;
        $this->edit_tipo_vehiculo               = $reserva->tipo_vehiculo;
        $this->edit_descripcion_vehiculo        = $reserva->descripcion_vehiculo;

        // Cargar plataformas para el selector
        $this->plataformas = DB::table('plat_reserva')->get();

        // ── HABITACIONES ──
        $this->edit_habitaciones_ids = DB::table('habitaciones_has_reservas')
            ->where('reservas_idreservas', $id)
            ->pluck('habitaciones_idhabitacion')
            ->map(fn($v) => (int) $v)
            ->toArray();

        $this->edit_habitaciones_disponibles = DB::table('habitaciones')
            ->where(function ($q) use ($id) {
                $q->where('estado', 'disponible')
                  ->orWhereIn('idhabitacion', function ($sub) use ($id) {
                      $sub->select('habitaciones_idhabitacion')
                          ->from('habitaciones_has_reservas')
                          ->where('reservas_idreservas', $id);
                  });
            })
            ->orderBy('no_habitacion')
            ->get();

        // ── Espacios de estacionamiento ──
        $todosEspacios = DB::table('estacionamiento')->get();
        $this->espacios_disponibles = $todosEspacios->filter(function ($espacio) use ($reserva) {
            return $espacio->estado === 'disponible' ||
                   $espacio->no_espacio == $reserva->estacionamiento_no_espacio;
        })->values();

        $this->mostrarModalEditar = true;
    }

    public function cerrarModalEditar()
    {
        $this->mostrarModalEditar = false;
        $this->reset([
            'editando_id', 'edit_folio', 'edit_fecha_reserva', 'edit_fecha_check_in',
            'edit_fecha_check_out', 'edit_no_personas', 'edit_estado',
            'edit_estacionamiento_no_espacio', 'edit_total_reserva',
            'edit_tipo_vehiculo', 'edit_descripcion_vehiculo',
            'edit_nom_completo', 'edit_tipo_identificacion', 'edit_direccion',
            'edit_pais_origen', 'edit_plataforma_id', 'edit_metodo_pago',
            'edit_habitaciones_ids', 'edit_habitaciones_disponibles',
            'espacios_disponibles', 'plataformas',
        ]);
    }

    public function actualizarReserva()
    {
        $this->validate([
            'edit_folio'             => 'required|string|max:50|unique:reservas,folio,' . $this->editando_id . ',idreservas',
            'edit_fecha_reserva'     => 'required|date',
            'edit_fecha_check_in'    => 'required|date',
            'edit_fecha_check_out'   => 'required|date|after:edit_fecha_check_in',
            'edit_no_personas'       => 'required|integer|min:1',
            'edit_estado'            => 'required|in:pendiente,confirmada,cancelada,completada',
            'edit_nom_completo'      => 'required|min:3',
            'edit_metodo_pago'       => 'nullable',
            'edit_total_reserva'     => 'nullable|numeric|min:0',
            'edit_habitaciones_ids'  => 'required|array|min:1',
            'edit_habitaciones_ids.*'=> 'exists:habitaciones,idhabitacion',
        ], [
            'edit_folio.required'            => 'El folio es obligatorio',
            'edit_folio.unique'              => 'Este folio ya está en uso por otra reserva',
            'edit_habitaciones_ids.required' => 'Debes seleccionar al menos una habitación',
            'edit_habitaciones_ids.min'      => 'Debes seleccionar al menos una habitación',
        ]);

        try {
            DB::beginTransaction();

            $reservaActual = DB::table('reservas')->where('idreservas', $this->editando_id)->first();
            if (!$reservaActual) throw new \Exception('Reserva no encontrada');

            $clienteActual = DB::table('clientes')
                ->where('idclientes', $reservaActual->clientes_idclientes)
                ->first();

            // ── Auditoría ──
            AuditService::logUpdated(
                'Reserva',
                $this->editando_id,
                [
                    'folio'                      => $reservaActual->folio,
                    'fecha_reserva'              => $reservaActual->fecha_reserva,
                    'fecha_check_in'             => $reservaActual->fecha_check_in,
                    'fecha_check_out'            => $reservaActual->fecha_check_out,
                    'no_personas'                => $reservaActual->no_personas,
                    'estado'                     => $reservaActual->estado,
                    'estacionamiento_no_espacio' => $reservaActual->estacionamiento_no_espacio,
                    'total_reserva'              => $reservaActual->total_reserva,
                    'metodo_pago'                => $reservaActual->metodo_pago,
                    'nom_completo'               => $clienteActual->nom_completo ?? null,
                    'tipo_identificacion'        => $clienteActual->tipo_identificacion ?? null,
                    'plataforma_id'              => $reservaActual->plat_reserva_idplat_reserva,
                ],
                [
                    'folio'                      => $this->edit_folio,
                    'fecha_reserva'              => $this->edit_fecha_reserva,
                    'fecha_check_in'             => $this->edit_fecha_check_in,
                    'fecha_check_out'            => $this->edit_fecha_check_out,
                    'no_personas'                => $this->edit_no_personas,
                    'estado'                     => $this->edit_estado,
                    'estacionamiento_no_espacio' => $this->edit_estacionamiento_no_espacio ?: null,
                    'total_reserva'              => $this->edit_total_reserva,
                    'metodo_pago'                => $this->edit_metodo_pago,
                    'nom_completo'               => $this->edit_nom_completo,
                    'tipo_identificacion'        => $this->edit_tipo_identificacion,
                    'plataforma_id'              => $this->edit_plataforma_id,
                ]
            );

            // ── Estacionamiento ──
            $estacionamientoAnterior = $reservaActual->estacionamiento_no_espacio;
            $estacionamientoNuevo    = $this->edit_estacionamiento_no_espacio ?: null;

            if ($estacionamientoAnterior != $estacionamientoNuevo) {
                if ($estacionamientoAnterior) {
                    DB::table('estacionamiento')
                        ->where('no_espacio', $estacionamientoAnterior)
                        ->update(['estado' => 'disponible']);
                }
                if ($estacionamientoNuevo) {
                    DB::table('estacionamiento')
                        ->where('no_espacio', $estacionamientoNuevo)
                        ->update(['estado' => 'ocupado']);
                }
            }

            // ── Actualizar reserva ──
            DB::table('reservas')
                ->where('idreservas', $this->editando_id)
                ->update([
                    'folio'                       => $this->edit_folio,           // ← NUEVO
                    'fecha_reserva'               => $this->edit_fecha_reserva,
                    'fecha_check_in'              => $this->edit_fecha_check_in,
                    'fecha_check_out'             => $this->edit_fecha_check_out,
                    'no_personas'                 => $this->edit_no_personas,
                    'estado'                      => $this->edit_estado,
                    'estacionamiento_no_espacio'  => $estacionamientoNuevo,
                    'total_reserva'               => $this->edit_total_reserva,
                    'tipo_vehiculo'               => $this->edit_tipo_vehiculo ?: null,
                    'descripcion_vehiculo'        => $this->edit_descripcion_vehiculo ?: null,
                    'metodo_pago'                 => $this->edit_metodo_pago,
                    'plat_reserva_idplat_reserva' => $this->edit_plataforma_id ?: null,
                    'updated_at'                  => now(),
                ]);

            // ── Actualizar cliente ──
            if ($clienteActual) {
                DB::table('clientes')
                    ->where('idclientes', $reservaActual->clientes_idclientes)
                    ->update([
                        'nom_completo'        => trim($this->edit_nom_completo),
                        'tipo_identificacion' => $this->edit_tipo_identificacion ?: null,
                        'direccion'           => $this->edit_direccion ?: null,
                        'pais_origen'         => $this->edit_pais_origen ?: null,
                    ]);
            }

            // ── HABITACIONES: recalcular ──
            $habitacionesAnteriores = DB::table('habitaciones_has_reservas')
                ->where('reservas_idreservas', $this->editando_id)
                ->pluck('habitaciones_idhabitacion')
                ->map(fn($v) => (int) $v)
                ->toArray();

            $habitacionesNuevas = array_map('intval', $this->edit_habitaciones_ids);
            $paraAgregar  = array_diff($habitacionesNuevas, $habitacionesAnteriores);
            $paraEliminar = array_diff($habitacionesAnteriores, $habitacionesNuevas);

            foreach ($paraEliminar as $habitacionId) {
                DB::table('habitaciones_has_reservas')
                    ->where('reservas_idreservas', $this->editando_id)
                    ->where('habitaciones_idhabitacion', $habitacionId)
                    ->delete();

                DB::table('habitaciones')
                    ->where('idhabitacion', $habitacionId)
                    ->update(['estado' => 'disponible']);
            }

            foreach ($paraAgregar as $habitacionId) {
                DB::table('habitaciones_has_reservas')->insert([
                    'habitaciones_idhabitacion' => $habitacionId,
                    'reservas_idreservas'       => $this->editando_id,
                ]);

                DB::table('habitaciones')
                    ->where('idhabitacion', $habitacionId)
                    ->update(['estado' => 'ocupada']);
            }

            DB::commit();

            session()->flash('message', 'Reserva actualizada exitosamente.');
            $this->cerrarModalEditar();
            $this->dispatch('reserva-actualizada');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al actualizar la reserva: ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════
    // CANCELAR / LIBERAR / ELIMINAR
    // ══════════════════════════════════════════════════════════

    public function eliminar($id)
    {
        try {
            DB::beginTransaction();
            $reserva = DB::table('reservas')->where('idreservas', $id)->first();

            if ($reserva) {
                AuditService::logUpdated('Reserva', $id,
                    ['estado' => $reserva->estado],
                    ['estado' => 'cancelada']
                );

                DB::table('reservas')->where('idreservas', $id)
                    ->update(['estado' => 'cancelada', 'updated_at' => now()]);

                $habitaciones = DB::table('habitaciones_has_reservas')
                    ->where('reservas_idreservas', $id)
                    ->pluck('habitaciones_idhabitacion');

                foreach ($habitaciones as $habitacionId) {
                    DB::table('habitaciones')
                        ->where('idhabitacion', $habitacionId)
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
                AuditService::logUpdated('Reserva', $id,
                    ['estado' => $reserva->estado],
                    ['estado' => 'completada']
                );

                DB::table('reservas')->where('idreservas', $id)
                    ->update(['estado' => 'completada', 'updated_at' => now()]);

                $habitaciones = DB::table('habitaciones_has_reservas')
                    ->where('reservas_idreservas', $id)
                    ->pluck('habitaciones_idhabitacion');

                foreach ($habitaciones as $habitacionId) {
                    DB::table('habitaciones')
                        ->where('idhabitacion', $habitacionId)
                        ->update(['estado' => 'disponible']);
                }

                if ($reserva->estacionamiento_no_espacio) {
                    DB::table('estacionamiento')
                        ->where('no_espacio', $reserva->estacionamiento_no_espacio)
                        ->update(['estado' => 'disponible']);
                }

                DB::commit();
                session()->flash('message', 'Reserva liberada exitosamente. Estado: COMPLETADA');
                $this->dispatch('reserva-actualizada');
            } else {
                session()->flash('error', 'Reserva no encontrada.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al liberar la reserva: ' . $e->getMessage());
        }
    }

    public function eliminarPermanente($id)
    {
        try {
            DB::beginTransaction();
            $reserva = DB::table('reservas')->where('idreservas', $id)->first();

            if ($reserva) {
                AuditService::logDeleted('Reserva', $id, [
                    'folio'           => $reserva->folio,
                    'cliente_id'      => $reserva->clientes_idclientes,
                    'estado'          => $reserva->estado,
                    'fecha_check_in'  => $reserva->fecha_check_in,
                    'fecha_check_out' => $reserva->fecha_check_out,
                ]);

                $habitaciones = DB::table('habitaciones_has_reservas')
                    ->where('reservas_idreservas', $id)
                    ->pluck('habitaciones_idhabitacion');

                foreach ($habitaciones as $habitacionId) {
                    DB::table('habitaciones')
                        ->where('idhabitacion', $habitacionId)
                        ->update(['estado' => 'disponible']);
                }

                if ($reserva->estacionamiento_no_espacio) {
                    DB::table('estacionamiento')
                        ->where('no_espacio', $reserva->estacionamiento_no_espacio)
                        ->update(['estado' => 'disponible']);
                }

                DB::table('habitaciones_has_reservas')->where('reservas_idreservas', $id)->delete();
                DB::table('reservas')->where('idreservas', $id)->delete();

                DB::commit();
                session()->flash('message', 'Reserva eliminada permanentemente.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════
    // RENDER
    // ══════════════════════════════════════════════════════════

    public function render()
    {
        $query = DB::table('reservas')
            ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
            ->leftJoin('plat_reserva', 'reservas.plat_reserva_idplat_reserva', '=', 'plat_reserva.idplat_reserva')
            ->leftJoin('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
            ->leftJoin('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
            ->select(
                'reservas.idreservas',
                'reservas.folio',
                'reservas.fecha_reserva',
                'reservas.fecha_check_in',
                'reservas.fecha_check_out',
                'reservas.no_personas',
                'reservas.estado',
                'reservas.estacionamiento_no_espacio',
                'reservas.total_reserva',
                'reservas.tipo_vehiculo',
                'reservas.descripcion_vehiculo',
                'clientes.nom_completo',
                'plat_reserva.nombre_plataforma',
                'plat_reserva.comision',
                // ← CORRECCIÓN: GROUP_CONCAT para mostrar TODAS las habitaciones
                DB::raw('GROUP_CONCAT(DISTINCT habitaciones.no_habitacion ORDER BY habitaciones.no_habitacion SEPARATOR ", ") as no_habitacion'),
                DB::raw('GROUP_CONCAT(DISTINCT habitaciones.tipo ORDER BY habitaciones.no_habitacion SEPARATOR ", ") as tipo_habitacion'),
                DB::raw('COUNT(DISTINCT habitaciones.idhabitacion) as total_habitaciones')
            )
            ->groupBy(
                'reservas.idreservas', 'reservas.folio', 'reservas.fecha_reserva',
                'reservas.fecha_check_in', 'reservas.fecha_check_out', 'reservas.no_personas',
                'reservas.estado', 'reservas.estacionamiento_no_espacio', 'reservas.total_reserva',
                'reservas.tipo_vehiculo', 'reservas.descripcion_vehiculo',
                'clientes.nom_completo', 'plat_reserva.nombre_plataforma', 'plat_reserva.comision'
            );

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('clientes.nom_completo', 'like', '%' . $this->search . '%')
                  ->orWhere('reservas.folio', 'like', '%' . $this->search . '%')
                  ->orWhere('reservas.idreservas', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->fecha_inicio && $this->fecha_fin) {
            $query->whereBetween('reservas.fecha_check_in', [$this->fecha_inicio, $this->fecha_fin]);
        } elseif ($this->fecha_inicio) {
            $query->where('reservas.fecha_check_in', '>=', $this->fecha_inicio);
        } elseif ($this->fecha_fin) {
            $query->where('reservas.fecha_check_in', '<=', $this->fecha_fin);
        }

        if ($this->estado_filtro) {
            $query->where('reservas.estado', $this->estado_filtro);
        }

        $reservas = $query->orderBy('reservas.idreservas', 'desc')->paginate(10);

        return view('livewire.reservas.index', [
            'reservas'                      => $reservas,
            'edit_metodo_pago'              => $this->edit_metodo_pago,
            'edit_habitaciones_ids'         => $this->edit_habitaciones_ids,
            'edit_habitaciones_disponibles' => $this->edit_habitaciones_disponibles,
            'espacios_disponibles'          => $this->espacios_disponibles,
            'editando_id'                   => $this->editando_id,
            'plataformas'                   => $this->plataformas,
        ]);
    }
}
