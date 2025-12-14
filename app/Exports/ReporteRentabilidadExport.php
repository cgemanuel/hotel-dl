<?php
// app/Exports/ReporteRentabilidadExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReporteRentabilidadExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $datos;
    protected $fecha_inicio;
    protected $fecha_fin;

    public function __construct($datos, $fecha_inicio, $fecha_fin)
    {
        $this->datos = $datos;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
    }

    public function array(): array
    {
        $data = [];

        // Encabezado del reporte
        $data[] = ['Hotel Don Luis - Reporte de Rentabilidad'];
        $data[] = [
            'Período: ' . \Carbon\Carbon::parse($this->fecha_inicio)->format('d/m/Y') .
            ' al ' . \Carbon\Carbon::parse($this->fecha_fin)->format('d/m/Y')
        ];
        $data[] = ['Generado: ' . now()->format('d/m/Y H:i')];
        $data[] = [];

        // Resumen General
        $data[] = ['RESUMEN GENERAL'];
        $data[] = [];
        $data[] = ['Concepto', 'Monto'];
        $data[] = [
            'Ingresos Brutos',
            '$' . number_format($this->datos['ingresos_totales']->subtotal ?? 0, 2)
        ];
        $data[] = [
            'Comisiones Totales',
            '$' . number_format($this->datos['ingresos_totales']->comisiones ?? 0, 2)
        ];
        $data[] = [
            'Ingresos Netos',
            '$' . number_format(($this->datos['ingresos_totales']->subtotal ?? 0) - ($this->datos['ingresos_totales']->comisiones ?? 0), 2)
        ];
        $data[] = [];

        // Tasa de Ocupación
        $data[] = ['TASA DE OCUPACIÓN'];
        $data[] = [];
        $data[] = ['Métrica', 'Valor'];
        $data[] = [
            'Tasa de Ocupación',
            number_format($this->datos['tasa_ocupacion'], 2) . '%'
        ];
        $data[] = [
            'Habitaciones-Día Ocupadas',
            $this->datos['habitaciones_dia_ocupadas']
        ];
        $data[] = [
            'Habitaciones-Día Disponibles',
            $this->datos['habitaciones_dia_disponibles']
        ];
        $data[] = [];

        // Rentabilidad por Tipo
        $data[] = ['RENTABILIDAD POR TIPO DE HABITACIÓN'];
        $data[] = [];
        $data[] = ['Tipo', 'Reservas', 'Ingresos', 'Ticket Promedio'];
        foreach ($this->datos['rentabilidad_tipo'] as $tipo) {
            $data[] = [
                ucfirst($tipo->tipo),
                $tipo->cantidad_reservas,
                '$' . number_format($tipo->ingresos, 2),
                '$' . number_format($tipo->ticket_promedio, 2)
            ];
        }
        $data[] = [];

        // Rentabilidad por Plataforma
        $data[] = ['RENTABILIDAD POR PLATAFORMA'];
        $data[] = [];
        $data[] = ['Plataforma', '% Comisión', 'Reservas', 'Ingresos Brutos', 'Comisión', 'Ingresos Netos'];
        foreach ($this->datos['rentabilidad_plataforma'] as $plat) {
            $data[] = [
                $plat->nombre_plataforma,
                $plat->comision . '%',
                $plat->cantidad_reservas,
                '$' . number_format($plat->ingresos_brutos, 2),
                '$' . number_format($plat->comision_total, 2),
                '$' . number_format($plat->ingresos_brutos - $plat->comision_total, 2)
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => '6366f1']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => ['font' => ['italic' => true, 'size' => 10]],
            3 => ['font' => ['italic' => true, 'size' => 10]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:D1');
                
                // Aplicar bordes a todas las tablas
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A1:F{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);
            },
        ];
    }

    public function title(): string
    {
        return 'Rentabilidad';
    }
}