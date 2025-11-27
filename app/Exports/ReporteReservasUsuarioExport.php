<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReporteReservasUsuarioExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    protected $reservas;
    protected $fecha_inicio;
    protected $fecha_fin;

    public function __construct($reservas, $fecha_inicio, $fecha_fin)
    {
        $this->reservas = $reservas;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
    }

    public function array(): array
    {
        $data = [];

        // Encabezado del reporte
        $data[] = ['Hotel Don Luis - Reporte de Reservas por Recepcionista'];
        $data[] = [
            'Período: ' . \Carbon\Carbon::parse($this->fecha_inicio)->format('d/m/Y') .
            ' al ' . \Carbon\Carbon::parse($this->fecha_fin)->format('d/m/Y')
        ];
        $data[] = ['Generado: ' . now()->format('d/m/Y H:i')];
        $data[] = []; // Fila vacía

        // Encabezados de columnas
        $data[] = [
            'Recepcionista',
            'Total Reservas',
            'Confirmadas',
            'Completadas',
            'Canceladas'
        ];

        // Datos de las reservas
        foreach ($this->reservas as $reserva) {
            $data[] = [
                $reserva->usuario,
                $reserva->total_reservas,
                $reserva->confirmadas,
                $reserva->completadas,
                $reserva->canceladas,
            ];
        }

        // Fila vacía y totales
        $data[] = [];

        if (count($this->reservas) > 0) {
            $total_reservas = collect($this->reservas)->sum('total_reservas');
            $total_confirmadas = collect($this->reservas)->sum('confirmadas');
            $total_completadas = collect($this->reservas)->sum('completadas');
            $total_canceladas = collect($this->reservas)->sum('canceladas');

            $data[] = [
                'TOTALES',
                $total_reservas,
                $total_confirmadas,
                $total_completadas,
                $total_canceladas,
            ];

            // Estadísticas adicionales
            $data[] = [];
            $data[] = ['Estadísticas'];
            $data[] = ['Promedio por recepcionista', number_format($total_reservas / count($this->reservas), 1)];
            $data[] = ['Recepcionistas activos', count($this->reservas)];

            // Tasa de conversión
            if ($total_reservas > 0) {
                $data[] = [
                    'Tasa de completadas',
                    number_format(($total_completadas / $total_reservas) * 100, 1) . '%'
                ];
                $data[] = [
                    'Tasa de canceladas',
                    number_format(($total_canceladas / $total_reservas) * 100, 1) . '%'
                ];
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        // Título principal
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '7c3aed'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Información del reporte
        $sheet->getStyle('A2:A3')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 10,
            ],
        ]);

        // Encabezados de columnas (fila 5)
        $sheet->getStyle('A5:E5')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '7c3aed'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ]);

        // Datos (desde fila 6 hasta cantidad de reservas + 5)
        $lastDataRow = 5 + count($this->reservas);
        $sheet->getStyle("A6:E{$lastDataRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Fila de totales
        $totalRow = $lastDataRow + 2;
        $sheet->getStyle("A{$totalRow}:E{$totalRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEF3C7'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Sección de estadísticas
        $statsRow = $totalRow + 2;
        $sheet->getStyle("A{$statsRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
        ]);

        // Alternar colores en filas de datos
        for ($row = 6; $row <= $lastDataRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9FAFB'],
                    ],
                ]);
            }
        }

        // Ajustar anchos de columnas
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);

        return [];
    }

    public function title(): string
    {
        return 'Reservas por Usuario';
    }
}
