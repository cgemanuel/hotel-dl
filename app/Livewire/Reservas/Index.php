<?php
// app/Livewire/Reservas/Index.php - ACTUALIZADO CON CAMPOS DE VEHÍCULO Y TOTAL

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

    // Campos nuevos para vehículo
    public $tipo_vehiculo_temp = '';
    public $descripcion_vehiculo_temp = '';
    public $mostrar_form_vehiculo = false;

    public $editando_id;
    public $edit_fecha_reserva;
    public $edit_fecha_check_in;
    public $edit_fecha_check_out;
    public $edit_no_personas;
    public $edit_estado;
    public $edit_estacionamiento_no_espacio;
    public $edit_total_reserva;
    public $edit_tipo_vehiculo;
    public $edit_descripcion_vehiculo;

    protected $queryString = ['search', 'fecha_inicio', 'fecha_fin', 'estado_filtro'];
    protected $listeners = ['reserva-creada' => '$refresh', 'reserva-eliminada' => '$refresh', 'reserva-actualizada' => '$refresh'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFechaInicio() { $this->resetPage(); }
    public function updatingFechaFin() { $this->resetPage(); }
    public function updatingEstadoFiltro() { $this->resetPage(); }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'fecha_inicio', 'fecha_fin', 'estado_filtro']);
        $this->resetPage();
    }

    public function asignarEstacionamiento($reservaId)
    {
        try {
            $this->reserva_para_estacionamiento = $reservaId;

            $reserva = DB::table('reservas')
                ->where('idreservas', $reservaId)
                ->first();

            if (!$reserva) {
                session()->flash('error', 'Reserva no encontrada.');
                return;
            }

            $todosEspacios = DB::table('estacionamiento')->get();

            $this->espacios_disponibles = $todosEspacios->filter(function($espacio) use ($reserva) {
                return $espacio->estado === 'disponible' ||
                       $espacio->no_espacio == $reserva->estacionamiento_no_espacio;
            })->values();

            $this->espacio_seleccionado = $reserva->estacionamiento_no_espacio ?? '';
            $this->tipo_vehiculo_temp = $reserva->tipo_vehiculo ?? '';
            $this->descripcion_vehiculo_temp = $reserva->descripcion_vehiculo ?? '';

            // Si ya tiene estacionamiento asignado, mostrar form de vehículo
            $this->mostrar_form_vehiculo = !empty($this->espacio_seleccionado);

            $this->mostrarModalEstacionamiento = true;

        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar estacionamiento: ' . $e->getMessage());
        }
    }

    public function updatedEspacioSeleccionado($value)
    {
        // Si se selecciona un espacio, mostrar formulario de vehículo
        $this->mostrar_form_vehiculo = !empty($value);

        // Si se deselecciona, limpiar campos de vehículo
        if (empty($value)) {
            $this->tipo_vehiculo_temp = '';
            $this->descripcion_vehiculo_temp = '';
        }
    }

    public function guardarEstacionamiento()
    {
        // Validar campos de vehículo si se asignó estacionamiento
        if ($this->espacio_seleccionado) {
            $this->validate([
                'espacio_seleccionado' => 'required',
                'tipo_vehiculo_temp' => 'required|string|max:100',
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

            if (!$reserva) {
                throw new \Exception('Reserva no encontrada');
            }

            // AUDITORÍA
            AuditService::logUpdated(
                'Reserva',
                $this->reserva_para_estacionamiento,
                [
                    'estacionamiento_no_espacio' => $reserva->estacionamiento_no_espacio,
                    'tipo_vehiculo' => $reserva->tipo_vehiculo,
                    'descripcion_vehiculo' => $reserva->descripcion_vehiculo,
                ],
                [
                    'estacionamiento_no_espacio' => $this->espacio_seleccionado ?: null,
                    'tipo_vehiculo' => $this->tipo_vehiculo_temp ?: null,
                    'descripcion_vehiculo' => $this->descripcion_vehiculo_temp ?: null,
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
                    'tipo_vehiculo' => $this->tipo_vehiculo_temp ?: null,
                    'descripcion_vehiculo' => $this->descripcion_vehiculo_temp ?: null,
                    'updated_at' => now()
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
        $this->mostrarModalEstacionamiento = false;
        $this->reserva_para_estacionamiento = null;
        $this->espacio_seleccionado = '';
        $this->espacios_disponibles = [];
        $this->tipo_vehiculo_temp = '';
        $this->descripcion_vehiculo_temp = '';
        $this->mostrar_form_vehiculo = false;
    }

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
                'clientes.telefono',
                'clientes.correo',
                'plat_reserva.nombre_plataforma',
                'plat_reserva.comision',
                DB::raw('MAX(habitaciones.no_habitacion) as no_habitacion'),
                DB::raw('MAX(habitaciones.tipo) as tipo_habitacion')
            )
            ->groupBy(
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
                'clientes.telefono',
                'clientes.correo',
                'plat_reserva.nombre_plataforma',
                'plat_reserva.comision'
            );

        if ($this->search) {
            $query->where(function($q) {
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
            'reservas' => $reservas
        ]);
    }

    public function ver($id)
    {
        try {
            $reserva = DB::table('reservas')
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
                $this->edit_total_reserva = $this->reservaSeleccionada->total_reserva;
                $this->edit_tipo_vehiculo = $this->reservaSeleccionada->tipo_vehiculo;
                $this->edit_descripcion_vehiculo = $this->reservaSeleccionada->descripcion_vehiculo;

                $todosEspacios = DB::table('estacionamiento')->get();
                $this->espacios_disponibles = $todosEspacios->filter(function($espacio) {
                    return $espacio->estado === 'disponible' ||
                           $espacio->no_espacio == $this->reservaSeleccionada->estacionamiento_no_espacio;
                })->values();

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
        $this->reset([
            'editando_id', 'edit_fecha_reserva', 'edit_fecha_check_in',
            'edit_fecha_check_out', 'edit_no_personas', 'edit_estado',
            'edit_estacionamiento_no_espacio', 'edit_total_reserva',
            'edit_tipo_vehiculo', 'edit_descripcion_vehiculo'
        ]);
    }

    public function actualizarReserva()
    {
        $this->validate([
            'edit_fecha_reserva' => 'required|date',
            'edit_fecha_check_in' => 'required|date',
            'edit_fecha_check_out' => 'required|date|after:edit_fecha_check_in',
            'edit_no_personas' => 'required|integer|min:1',
            'edit_estado' => 'required|in:pendiente,confirmada,cancelada,completada',
            'edit_total_reserva' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $reservaActual = DB::table('reservas')->where('idreservas', $this->editando_id)->first();

            if (!$reservaActual) {
                throw new \Exception('Reserva no encontrada');
            }

            AuditService::logUpdated(
                'Reserva',
                $this->editando_id,
                [
                    'fecha_reserva' => $reservaActual->fecha_reserva,
                    'fecha_check_in' => $reservaActual->fecha_check_in,
                    'fecha_check_out' => $reservaActual->fecha_check_out,
                    'no_personas' => $reservaActual->no_personas,
                    'estado' => $reservaActual->estado,
                    'estacionamiento_no_espacio' => $reservaActual->estacionamiento_no_espacio,
                    'total_reserva' => $reservaActual->total_reserva,
                    'tipo_vehiculo' => $reservaActual->tipo_vehiculo,
                    'descripcion_vehiculo' => $reservaActual->descripcion_vehiculo,
                ],
                [
                    'fecha_reserva' => $this->edit_fecha_reserva,
                    'fecha_check_in' => $this->edit_fecha_check_in,
                    'fecha_check_out' => $this->edit_fecha_check_out,
                    'no_personas' => $this->edit_no_personas,
                    'estado' => $this->edit_estado,
                    'estacionamiento_no_espacio' => $this->edit_estacionamiento_no_espacio ?: null,
                    'total_reserva' => $this->edit_total_reserva,
                    'tipo_vehiculo' => $this->edit_tipo_vehiculo ?: null,
                    'descripcion_vehiculo' => $this->edit_descripcion_vehiculo ?: null,
                ]
            );

            $estacionamientoAnterior = $reservaActual->estacionamiento_no_espacio;
            $estacionamientoNuevo = $this->edit_estacionamiento_no_espacio ?: null;

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

            DB::table('reservas')
                ->where('idreservas', $this->editando_id)
                ->update([
                    'fecha_reserva' => $this->edit_fecha_reserva,
                    'fecha_check_in' => $this->edit_fecha_check_in,
                    'fecha_check_out' => $this->edit_fecha_check_out,
                    'no_personas' => $this->edit_no_personas,
                    'estado' => $this->edit_estado,
                    'estacionamiento_no_espacio' => $estacionamientoNuevo,
                    'total_reserva' => $this->edit_total_reserva,
                    'tipo_vehiculo' => $this->edit_tipo_vehiculo ?: null,
                    'descripcion_vehiculo' => $this->edit_descripcion_vehiculo ?: null,
                    'updated_at' => now()
                ]);

            DB::commit();

            session()->flash('message', 'Reserva actualizada exitosamente.');
            $this->cerrarModalEditar();
            $this->dispatch('reserva-actualizada');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al actualizar la reserva: ' . $e->getMessage());
        }
    }

    public function eliminar($id)
    {
        try {
            DB::beginTransaction();

            $reserva = DB::table('reservas')->where('idreservas', $id)->first();

            if ($reserva) {
                AuditService::logUpdated(
                    'Reserva',
                    $id,
                    ['estado' => $reserva->estado],
                    ['estado' => 'cancelada']
                );

                DB::table('reservas')
                    ->where('idreservas', $id)
                    ->update([
                        'estado' => 'cancelada',
                        'updated_at' => now()
                    ]);

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
                AuditService::logUpdated(
                    'Reserva',
                    $id,
                    ['estado' => $reserva->estado],
                    ['estado' => 'completada']
                );

                DB::table('reservas')
                    ->where('idreservas', $id)
                    ->update([
                        'estado' => 'completada',
                        'updated_at' => now()
                    ]);

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
        if (auth()->user()->rol !== 'gerente') {
            session()->flash('error', 'No tienes permisos para eliminar reservas.');
            return;
        }

        try {
            DB::beginTransaction();

            $reserva = DB::table('reservas')->where('idreservas', $id)->first();

            if ($reserva) {
                AuditService::logDeleted(
                    'Reserva',
                    $id,
                    [
                        'folio' => $reserva->folio,
                        'cliente_id' => $reserva->clientes_idclientes,
                        'estado' => $reserva->estado,
                        'fecha_check_in' => $reserva->fecha_check_in,
                        'fecha_check_out' => $reserva->fecha_check_out,
                    ]
                );

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

                DB::table('habitaciones_has_reservas')
                    ->where('reservas_idreservas', $id)
                    ->delete();

                DB::table('reservas')->where('idreservas', $id)->delete();

                DB::commit();
                session()->flash('message', 'Reserva eliminada permanentemente.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
}
