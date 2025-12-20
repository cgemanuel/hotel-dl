<?php

namespace App\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportesIngresos extends Component
{
    // Filtros
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $tipo_reporte = 'metodos_pago'; // 'metodos_pago' o 'reservas_usuario'

    // Datos del reporte
    public $totales_metodos = [];
    public $reservas_usuario = [];
    public $reporte_generado = false;

    public function mount()
    {
        // Establecer fechas por defecto (mes actual)
        $this->fecha_inicio = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = now()->endOfMonth()->format('Y-m-d');
    }

    public function generarReporte()
    {
        $this->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'tipo_reporte' => 'required|in:metodos_pago,reservas_usuario',
        ], [
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior a la fecha de inicio',
        ]);

        if ($this->tipo_reporte === 'metodos_pago') {
            $this->generarReporteMetodosPago();
        } else {
            $this->generarReporteReservasUsuario();
        }

        $this->reporte_generado = true;
    }

    private function generarReporteMetodosPago()
    {
        $reservas = DB::table('reservas')
            ->whereIn('estado', ['confirmada', 'completada'])
            ->whereBetween('fecha_reserva', [$this->fecha_inicio, $this->fecha_fin])
            ->get();

        // Calcular totales por método de pago
        $this->totales_metodos = [
            'efectivo' => 0,
            'tarjeta' => 0,
            'transferencia' => 0,
            'combinado' => 0,
            'total_general' => 0,
            'cantidad_reservas' => $reservas->count(),
        ];

        foreach ($reservas as $reserva) {
            // Para pagos simples
            if (in_array($reserva->metodo_pago, ['efectivo', 'tarjeta', 'transferencia'])) {
                $total_reserva = $this->calcularTotalReserva($reserva);
                $this->totales_metodos[$reserva->metodo_pago] += $total_reserva;
                $this->totales_metodos['total_general'] += $total_reserva;
            }

            // Para pagos combinados
            if ($reserva->metodo_pago === 'combinado') {
                $this->totales_metodos['efectivo'] += $reserva->monto_efectivo ?? 0;
                $this->totales_metodos['tarjeta'] += $reserva->monto_tarjeta ?? 0;
                $this->totales_metodos['transferencia'] += $reserva->monto_transferencia ?? 0;

                $total_combinado = ($reserva->monto_efectivo ?? 0) +
                                   ($reserva->monto_tarjeta ?? 0) +
                                   ($reserva->monto_transferencia ?? 0);

                $this->totales_metodos['combinado'] += $total_combinado;
                $this->totales_metodos['total_general'] += $total_combinado;
            }
        }
    }

    private function generarReporteReservasUsuario()
    {
        // Si el usuario es recepcionista, solo ve sus propias reservas
        $query = DB::table('reservas')
            ->join('users', 'reservas.created_by', '=', 'users.id')
            ->leftJoin('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
            ->leftJoin('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
            ->select(
                'users.name as usuario',
                DB::raw('COUNT(DISTINCT reservas.idreservas) as total_reservas'),
                DB::raw('SUM(CASE WHEN reservas.estado = "confirmada" THEN 1 ELSE 0 END) as confirmadas'),
                DB::raw('SUM(CASE WHEN reservas.estado = "completada" THEN 1 ELSE 0 END) as completadas'),
                DB::raw('SUM(CASE WHEN reservas.estado = "cancelada" THEN 1 ELSE 0 END) as canceladas')
            )
            ->whereBetween('reservas.created_at', [$this->fecha_inicio, $this->fecha_fin])
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_reservas', 'desc');

        // Si es recepcionista, filtrar solo sus reservas
        if (auth()->user()->rol === 'recepcionista') {
            $query->where('reservas.created_by', auth()->id());
        }

        $this->reservas_usuario = $query->get()->toArray();
    }

    private function calcularTotalReserva($reserva)
    {
        // Usar directamente el campo total_reserva
        return $reserva->total_reserva ?? 0;
    }


    public function limpiarReporte()
    {
        $this->reset(['totales_metodos', 'reservas_usuario', 'reporte_generado']);
    }

    public function exportarPDF()
    {
        if (!$this->reporte_generado) {
            session()->flash('error', 'Debes generar un reporte primero');
            return;
        }

        try {
            // Preparar datos para el PDF
            $datos = [
                'tipo_reporte' => $this->tipo_reporte,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin,
                'totales_metodos' => $this->totales_metodos,
                'reservas_usuario' => $this->reservas_usuario,
                'fecha_generacion' => now()->format('d/m/Y H:i'),
                'usuario' => auth()->user()->name,
            ];

            // Generar HTML para el PDF
            $html = view('livewire.reportes.pdf-template', $datos)->render();

            // Usar DomPDF para generar el PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('letter', 'portrait');

            $filename = $this->tipo_reporte === 'metodos_pago'
                ? 'reporte_ingresos_' . date('Y-m-d') . '.pdf'
                : 'reporte_reservas_usuario_' . date('Y-m-d') . '.pdf';

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $filename);

        } catch (\Exception $e) {
            session()->flash('error', 'Error al generar PDF: ' . $e->getMessage());
        }
    }

    public function exportarExcel()
    {
        if (!$this->reporte_generado) {
            session()->flash('error', 'Debes generar un reporte primero');
            return;
        }

        try {
            $filename = $this->tipo_reporte === 'metodos_pago'
                ? 'reporte_ingresos_' . date('Y-m-d') . '.xlsx'
                : 'reporte_reservas_usuario_' . date('Y-m-d') . '.xlsx';

            if ($this->tipo_reporte === 'metodos_pago') {
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\ReporteIngresosExport(
                        $this->totales_metodos,
                        $this->fecha_inicio,
                        $this->fecha_fin
                    ),
                    $filename
                );
            } else {
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\ReporteReservasUsuarioExport(
                        $this->reservas_usuario,
                        $this->fecha_inicio,
                        $this->fecha_fin
                    ),
                    $filename
                );
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error al generar Excel: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.reportes.reportes-ingresos');
    }
}
