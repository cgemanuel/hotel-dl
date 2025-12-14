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

class ReporteTemporadasExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $datosTemporadas;
    protected $fecha_inicio;
    protected $fecha_fin;

    public function __construct($datosTemporadas, $fecha_inicio, $fecha_fin)
    {
        $this->datosTemporadas = $datosTemporadas;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
    }

    public function array(): array
    {
        $data = [];

        // Encabezado del reporte
        $data[] = ['Hotel Don Luis - Reporte de Temporadas'];
        $data[] = [
            'Período: ' . \Carbon\Carbon::parse($this->fecha_inicio)->format('d/m/Y') .
            ' al ' . \Carbon\Carbon::parse($this->fecha_fin)->format('d/m/Y')
        ];
        $data[] = ['Generado: ' . now()->format('d/m/Y H:i')];
        $data[] = ['Usuario: ' . auth()->user()->name];
        $data[] = []; // Fila vacía

        // Encabezados de la tabla
        $data[] = ['Temporada', 'Período', 'Total Reservas', 'Ingresos', 'Precio Promedio', 'Estancia Promedio (días)'];

        // Datos de cada temporada
        foreach ($this->datosTemporadas as $temporada) {
            $data[] = [
                $temporada['nombre'],
                $temporada['periodo'],
                $temporada['stats']->total_reservas ?? 0,
                '$' . number_format($temporada['stats']->ingresos ?? 0, 2),
                '$' . number_format($temporada['stats']->precio_promedio ?? 0, 2),
                number_format($temporada['stats']->estancia_promedio ?? 0, 1)
            ];
        }

        $data[] = []; // Fila vacía

        // Totales
        $totalReservas = collect($this->datosTemporadas)->sum(fn($t) => $t['stats']->total_reservas ?? 0);
        $totalIngresos = collect($this->datosTemporadas)->sum(fn($t) => $t['stats']->ingresos ?? 0);

        $data[] = [
            'TOTALES',
            '',
            $totalReservas,
            '$' . number_format($totalIngresos, 2),
            '',
            ''
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
                $lastRow = 6 + count($this->datosTemporadas) + 2;
                $sheet->getStyle('A6:F' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);

                // Centrar contenido
                $sheet->getStyle('B6:F' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Estilo de fila de totales
                $sheet->getStyle('A' . $lastRow . ':F' . $lastRow)->applyFromArray([
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
        return 'Reporte de Temporadas';
    }
}