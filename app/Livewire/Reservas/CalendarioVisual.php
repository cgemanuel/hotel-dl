<?php

namespace App\Livewire\Reservas;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CalendarioVisual extends Component
{
    public $mesActual;
    public $anioActual;
    public $fechaSeleccionada = null;
    public $reservasDelDia = [];
    public $mostrarModalDia = false;

    public function mount()
    {
        $this->mesActual = now()->month;
        $this->anioActual = now()->year;
    }

    public function mesAnterior()
    {
        if ($this->mesActual == 1) {
            $this->mesActual = 12;
            $this->anioActual--;
        } else {
            $this->mesActual--;
        }
    }

    public function mesSiguiente()
    {
        if ($this->mesActual == 12) {
            $this->mesActual = 1;
            $this->anioActual++;
        } else {
            $this->mesActual++;
        }
    }

    public function irHoy()
    {
        $this->mesActual = now()->month;
        $this->anioActual = now()->year;
    }

    public function verReservasDelDia($fecha)
    {
        $this->fechaSeleccionada = $fecha;

        $this->reservasDelDia = DB::table('reservas')
            ->join('clientes', 'reservas.clientes_idclientes', '=', 'clientes.idclientes')
            ->leftJoin('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
            ->leftJoin('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
            ->where(function($query) use ($fecha) {
                $query->whereDate('reservas.fecha_check_in', $fecha)
                      ->orWhereDate('reservas.fecha_check_out', $fecha)
                      ->orWhere(function($q) use ($fecha) {
                          $q->where('reservas.fecha_check_in', '<=', $fecha)
                            ->where('reservas.fecha_check_out', '>=', $fecha);
                      });
            })
            ->whereIn('reservas.estado', ['confirmada', 'pendiente'])
            ->select(
                'reservas.*',
                'clientes.nom_completo',
                'clientes.telefono',
                DB::raw('MAX(habitaciones.no_habitacion) as no_habitacion')
            )
            ->groupBy(
                'reservas.idreservas', 'reservas.folio', 'reservas.fecha_reserva',
                'reservas.fecha_check_in', 'reservas.fecha_check_out', 'reservas.no_personas',
                'reservas.estado', 'reservas.metodo_pago', 'reservas.monto_efectivo',
                'reservas.monto_tarjeta', 'reservas.monto_transferencia',
                'reservas.estacionamiento_no_espacio', 'reservas.plat_reserva_idplat_reserva',
                'reservas.clientes_idclientes', 'reservas.created_by', 'reservas.created_at',
                'reservas.updated_at', 'reservas.facturacion',
                'clientes.nom_completo', 'clientes.telefono'
            )
            ->get();

        $this->mostrarModalDia = true;
    }

    public function cerrarModal()
    {
        $this->mostrarModalDia = false;
        $this->fechaSeleccionada = null;
        $this->reservasDelDia = [];
    }

    public function render()
    {
        $primerDia = Carbon::create($this->anioActual, $this->mesActual, 1);
        $ultimoDia = $primerDia->copy()->endOfMonth();

        // Obtener todas las reservas del mes para mostrar en el calendario
        $reservasMes = DB::table('reservas')
            ->whereYear('fecha_check_in', $this->anioActual)
            ->whereMonth('fecha_check_in', $this->mesActual)
            ->whereIn('estado', ['confirmada', 'pendiente'])
            ->orWhere(function($query) {
                $query->whereYear('fecha_check_out', $this->anioActual)
                      ->whereMonth('fecha_check_out', $this->mesActual)
                      ->whereIn('estado', ['confirmada', 'pendiente']);
            })
            ->select('fecha_check_in', 'fecha_check_out', 'estado')
            ->get();

        // Agrupar reservas por día
        $reservasPorDia = [];
        foreach ($reservasMes as $reserva) {
            $inicio = Carbon::parse($reserva->fecha_check_in);
            $fin = Carbon::parse($reserva->fecha_check_out);

            while ($inicio->lte($fin)) {
                if ($inicio->month == $this->mesActual && $inicio->year == $this->anioActual) {
                    $dia = $inicio->day;
                    if (!isset($reservasPorDia[$dia])) {
                        $reservasPorDia[$dia] = 0;
                    }
                    $reservasPorDia[$dia]++;
                }
                $inicio->addDay();
            }
        }

        return view('livewire.reservas.calendario-visual', [
            'primerDia' => $primerDia,
            'ultimoDia' => $ultimoDia,
            'reservasPorDia' => $reservasPorDia,
        ]);
    }
}
