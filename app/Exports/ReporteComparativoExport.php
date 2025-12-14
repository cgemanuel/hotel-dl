<?php

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

class ReporteComparativoExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $datosComparativos;
    protected $fecha_inicio;
    protected $fecha_fin;

    public function __construct($datosComparativos, $fecha_inicio, $fecha_fin)
    {
        $this->datosComparativos = $datosComparativos;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
    }

    public function array(): array
    {
        $data = [];

        // Encabezado del reporte
        $data[] = ['Hotel Don Luis - Reporte Comparativo'];
        $data[] = [
            'Comparación: ' . $this->datosComparativos['anio_anterior'] . ' vs ' . $this->datosComparativos['anio_actual']
        ];
        $data[] = ['Generado: ' . now()->format('d/m/Y H:i')];
        $data[] = ['Usuario: ' . auth()->user()->name];
        $data[] = []; // Fila vacía

        // Encabezados de la tabla
        $data[] = [
            'Mes',
            'Reservas ' . $this->datosComparativos['anio_anterior'],
            'Ingresos ' . $this->datosComparativos['anio_anterior'],
            'Reservas ' . $this->datosComparativos['anio_actual'],
            'Ingresos ' . $this->datosComparativos['anio_actual'],
            'Variación %'
        ];

        // Datos mensuales
        foreach ($this->datosComparativos['meses_actual'] as $index => $mesActual) {
            $mesAnterior = $this->datosComparativos['meses_anterior'][$index];
            
            $reservasAnterior = $mesAnterior['reservas'];
            $reservasActual = $mesActual['reservas'];
            $variacion = $reservasAnterior > 0 ? (($reservasActual - $reservasAnterior) / $reservasAnterior) * 100 : 0;

            $data[] = [
                $mesActual['mes'],
                $reservasAnterior,
                '$' . number_format($mesAnterior['ingresos'], 2),
                $reservasActual,
                '$' . number_format($mesActual['ingresos'], 2),
                number_format($variacion, 1) . '%'
            ];
        }

        $data[] = []; // Fila vacía

        // Totales
        $totalReservasAnterior = collect($this->datosComparativos['meses_anterior'])->sum('reservas');
        $totalIngresosAnterior = collect($this->datosComparativos['meses_anterior'])->sum('ingresos');
        $totalReservasActual = collect($this->datosComparativos['meses_actual'])->sum('reservas');
        $totalIngresosActual = collect($this->datosComparativos['meses_actual'])->sum('ingresos');
        $variacionTotal = $totalReservasAnterior > 0 ? (($totalReservasActual - $totalReservasAnterior) / $totalReservasAnterior) * 100 : 0;

        $data[] = [
            'TOTALES',
            $totalReservasAnterior,
            '$' . number_format($totalIngresosAnterior, 2),
            $totalReservasActual,
            '$' . number_format($totalIngresosActual, 2),
            number_format($variacionTotal, 1) . '%'
        ];

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
                'font' => [
                    'bold' => true,
                    'size' => 18,
                    'color' => ['rgb' => '6366f1'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            2 => ['font' => ['italic' => true, 'size' => 10]],
            3 => ['font' => ['italic' => true, 'size' => 10]],
            4 => ['font' => ['italic' => true, 'size' => 10]],
            6 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '6366f1'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:F1');

                // Aplicar bordes a la tabla
                $sheet->getStyle('A6:F19')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);

                // Centrar contenido
                $sheet->getStyle('B6:F19')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Estilo de fila de totales
                $sheet->getStyle('A19:F19')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEF3C7'],
                    ],
                ]);
            },
        ];
    }

    public function title(): string
    {
        return 'Reporte Comparativo';
    }
}