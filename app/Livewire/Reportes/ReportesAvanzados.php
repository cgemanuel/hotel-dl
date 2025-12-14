<?php
// app/Livewire/Reportes/ReportesAvanzados.php

namespace App\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportesAvanzados extends Component
{
    public $tipoReporte = 'rentabilidad'; // rentabilidad, temporadas, comparativas
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $anio_comparacion = '';

    // Datos de reportes
    public $datosRentabilidad = [];
    public $datosTemporadas = [];
    public $datosComparativos = [];
    public $reporte_generado = false;

    public function mount()
    {
        $this->fecha_inicio = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = now()->endOfMonth()->format('Y-m-d');
        $this->anio_comparacion = now()->year - 1;
    }

    public function generarReporte()
    {
        $this->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ], [
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior a la fecha de inicio',
        ]);

        if ($this->tipoReporte === 'rentabilidad') {
            $this->generarReporteRentabilidad();
        } elseif ($this->tipoReporte === 'temporadas') {
            $this->generarReporteTemporadas();
        } elseif ($this->tipoReporte === 'comparativas') {
            $this->generarReporteComparativo();
        }

        $this->reporte_generado = true;
    }

    private function generarReporteRentabilidad()
    {
        // Ingresos totales
        $ingresosTotales = DB::table('reservas')
            ->join('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
            ->join('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
            ->leftJoin('plat_reserva', 'reservas.plat_reserva_idplat_reserva', '=', 'plat_reserva.idplat_reserva')
            ->whereBetween('reservas.fecha_reserva', [$this->fecha_inicio, $this->fecha_fin])
            ->whereIn('reservas.estado', ['confirmada', 'completada'])
            ->select(
                DB::raw('SUM(habitaciones.precio * GREATEST(DATEDIFF(reservas.fecha_check_out, reservas.fecha_check_in), 1)) as subtotal'),
                DB::raw('SUM((habitaciones.precio * GREATEST(DATEDIFF(reservas.fecha_check_out, reservas.fecha_check_in), 1)) * (COALESCE(plat_reserva.comision, 0) / 100)) as comisiones')
            )
            ->first();

        // Rentabilidad por tipo de habitación
        $rentabilidadPorTipo = DB::table('reservas')
            ->join('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
            ->join('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
            ->whereBetween('reservas.fecha_reserva', [$this->fecha_inicio, $this->fecha_fin])
            ->whereIn('reservas.estado', ['confirmada', 'completada'])
            ->select(
                'habitaciones.tipo',
                DB::raw('COUNT(DISTINCT reservas.idreservas) as cantidad_reservas'),
                DB::raw('SUM(habitaciones.precio * GREATEST(DATEDIFF(reservas.fecha_check_out, reservas.fecha_check_in), 1)) as ingresos'),
                DB::raw('AVG(habitaciones.precio * GREATEST(DATEDIFF(reservas.fecha_check_out, reservas.fecha_check_in), 1)) as ticket_promedio')
            )
            ->groupBy('habitaciones.tipo')
            ->get();

        // Rentabilidad por plataforma
        $rentabilidadPorPlataforma = DB::table('reservas')
            ->join('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
            ->join('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
            ->join('plat_reserva', 'reservas.plat_reserva_idplat_reserva', '=', 'plat_reserva.idplat_reserva')
            ->whereBetween('reservas.fecha_reserva', [$this->fecha_inicio, $this->fecha_fin])
            ->whereIn('reservas.estado', ['confirmada', 'completada'])
            ->select(
                'plat_reserva.nombre_plataforma',
                'plat_reserva.comision',
                DB::raw('COUNT(DISTINCT reservas.idreservas) as cantidad_reservas'),
                DB::raw('SUM(habitaciones.precio * GREATEST(DATEDIFF(reservas.fecha_check_out, reservas.fecha_check_in), 1)) as ingresos_brutos'),
                DB::raw('SUM((habitaciones.precio * GREATEST(DATEDIFF(reservas.fecha_check_out, reservas.fecha_check_in), 1)) * (plat_reserva.comision / 100)) as comision_total')
            )
            ->groupBy('plat_reserva.idplat_reserva', 'plat_reserva.nombre_plataforma', 'plat_reserva.comision')
            ->get();

        // Tasa de ocupación
        $diasEnPeriodo = Carbon::parse($this->fecha_inicio)->diffInDays(Carbon::parse($this->fecha_fin)) + 1;
        $totalHabitaciones = DB::table('habitaciones')->count();
        $habitacionesDiaDisponibles = $totalHabitaciones * $diasEnPeriodo;

        $habitacionesDiaOcupadas = DB::table('reservas')
            ->whereBetween('fecha_check_in', [$this->fecha_inicio, $this->fecha_fin])
            ->whereIn('estado', ['confirmada', 'completada'])
            ->get()
            ->sum(function($reserva) {
                $inicio = Carbon::parse($reserva->fecha_check_in);
                $fin = Carbon::parse($reserva->fecha_check_out);
                return $inicio->diffInDays($fin);
            });

        $tasaOcupacion = $habitacionesDiaDisponibles > 0
            ? ($habitacionesDiaOcupadas / $habitacionesDiaDisponibles) * 100
            : 0;

        $this->datosRentabilidad = [
            'ingresos_totales' => $ingresosTotales,
            'rentabilidad_tipo' => $rentabilidadPorTipo,
            'rentabilidad_plataforma' => $rentabilidadPorPlataforma,
            'tasa_ocupacion' => $tasaOcupacion,
            'habitaciones_dia_ocupadas' => $habitacionesDiaOcupadas,
            'habitaciones_dia_disponibles' => $habitacionesDiaDisponibles,
        ];
    }

    private function generarReporteTemporadas()
    {
        // Dividir el año en temporadas
        $temporadas = [
            'Baja' => ['01-01', '03-31'],
            'Media' => ['04-01', '06-30'],
            'Alta' => ['07-01', '09-30'],
            'Fin de Año' => ['10-01', '12-31'],
        ];

        $datosTemporadas = [];
        $anio = Carbon::parse($this->fecha_inicio)->year;

        foreach ($temporadas as $nombre => $rango) {
            $inicio = Carbon::parse("$anio-{$rango[0]}");
            $fin = Carbon::parse("$anio-{$rango[1]}");

            $stats = DB::table('reservas')
                ->join('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
                ->join('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
                ->whereBetween('reservas.fecha_reserva', [$inicio, $fin])
                ->whereIn('reservas.estado', ['confirmada', 'completada'])
                ->select(
                    DB::raw('COUNT(DISTINCT reservas.idreservas) as total_reservas'),
                    DB::raw('SUM(habitaciones.precio * GREATEST(DATEDIFF(reservas.fecha_check_out, reservas.fecha_check_in), 1)) as ingresos'),
                    DB::raw('AVG(habitaciones.precio) as precio_promedio'),
                    DB::raw('AVG(GREATEST(DATEDIFF(reservas.fecha_check_out, reservas.fecha_check_in), 1)) as estancia_promedio')
                )
                ->first();

            $datosTemporadas[] = [
                'nombre' => $nombre,
                'periodo' => $inicio->format('d/m') . ' - ' . $fin->format('d/m'),
                'stats' => $stats,
            ];
        }

        $this->datosTemporadas = $datosTemporadas;
    }

    private function generarReporteComparativo()
    {
        $anioActual = Carbon::parse($this->fecha_inicio)->year;
        $anioAnterior = $this->anio_comparacion;

        $mesesAnioActual = [];
        $mesesAnioAnterior = [];

        for ($mes = 1; $mes <= 12; $mes++) {
            // Datos año actual
            $statsActual = DB::table('reservas')
                ->join('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
                ->join('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
                ->whereYear('reservas.fecha_reserva', $anioActual)
                ->whereMonth('reservas.fecha_reserva', $mes)
                ->whereIn('reservas.estado', ['confirmada', 'completada'])
                ->select(
                    DB::raw('COUNT(DISTINCT reservas.idreservas) as total_reservas'),
                    DB::raw('SUM(habitaciones.precio * GREATEST(DATEDIFF(reservas.fecha_check_out, reservas.fecha_check_in), 1)) as ingresos')
                )
                ->first();

            // Datos año anterior
            $statsAnterior = DB::table('reservas')
                ->join('habitaciones_has_reservas', 'reservas.idreservas', '=', 'habitaciones_has_reservas.reservas_idreservas')
                ->join('habitaciones', 'habitaciones_has_reservas.habitaciones_idhabitacion', '=', 'habitaciones.idhabitacion')
                ->whereYear('reservas.fecha_reserva', $anioAnterior)
                ->whereMonth('reservas.fecha_reserva', $mes)
                ->whereIn('reservas.estado', ['confirmada', 'completada'])
                ->select(
                    DB::raw('COUNT(DISTINCT reservas.idreservas) as total_reservas'),
                    DB::raw('SUM(habitaciones.precio * GREATEST(DATEDIFF(reservas.fecha_check_out, reservas.fecha_check_in), 1)) as ingresos')
                )
                ->first();

            $mesesAnioActual[] = [
                'mes' => Carbon::create()->month($mes)->format('M'),
                'reservas' => $statsActual->total_reservas ?? 0,
                'ingresos' => $statsActual->ingresos ?? 0,
            ];

            $mesesAnioAnterior[] = [
                'mes' => Carbon::create()->month($mes)->format('M'),
                'reservas' => $statsAnterior->total_reservas ?? 0,
                'ingresos' => $statsAnterior->ingresos ?? 0,
            ];
        }

        $this->datosComparativos = [
            'anio_actual' => $anioActual,
            'anio_anterior' => $anioAnterior,
            'meses_actual' => $mesesAnioActual,
            'meses_anterior' => $mesesAnioAnterior,
        ];
    }

    public function limpiarReporte()
    {
        $this->reset(['datosRentabilidad', 'datosTemporadas', 'datosComparativos', 'reporte_generado']);
    }

    public function exportarPDF()
        {
            if (!$this->reporte_generado) {
                session()->flash('error', 'Debes generar un reporte primero');
                return;
            }

            try {
                $datos = [
                    'tipo_reporte' => $this->tipoReporte,
                    'fecha_inicio' => $this->fecha_inicio,
                    'fecha_fin' => $this->fecha_fin,
                    'datosRentabilidad' => $this->datosRentabilidad,
                    'datosTemporadas' => $this->datosTemporadas,
                    'datosComparativos' => $this->datosComparativos,
                    'fecha_generacion' => now()->format('d/m/Y H:i'),
                    'usuario' => auth()->user()->name,
                ];

                $html = view('livewire.reportes.pdf-avanzado-template', $datos)->render();
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                $pdf->setPaper('letter', 'portrait');

                $filename = 'reporte_' . $this->tipoReporte . '_' . date('Y-m-d') . '.pdf';

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
                $filename = 'reporte_' . $this->tipoReporte . '_' . date('Y-m-d') . '.xlsx';

                if ($this->tipoReporte === 'rentabilidad') {
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\ReporteRentabilidadExport(
                            $this->datosRentabilidad,
                            $this->fecha_inicio,
                            $this->fecha_fin
                        ),
                        $filename
                    );
                } elseif ($this->tipoReporte === 'temporadas') {
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\ReporteTemporadasExport(
                            $this->datosTemporadas,
                            $this->fecha_inicio,
                            $this->fecha_fin
                        ),
                        $filename
                    );
                } elseif ($this->tipoReporte === 'comparativas') {
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\ReporteComparativoExport(
                            $this->datosComparativos,
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
        return view('livewire.reportes.reportes-avanzados');
    }
}
