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
use PhpOffice\PhpSpreadsheet\Style\Color;

class ReporteIngresosExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $totales;
    protected $fecha_inicio;
    protected $fecha_fin;

    public function __construct($totales, $fecha_inicio, $fecha_fin)
    {
        $this->totales = $totales;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
    }

    /**
     * Retorna los datos del reporte como array
     */
    public function array(): array
    {
        $data = [];

        // Encabezado del reporte
        $data[] = ['Hotel Don Luis - Reporte de Ingresos por Método de Pago'];
        $data[] = [
            'Período: ' . \Carbon\Carbon::parse($this->fecha_inicio)->format('d/m/Y') .
            ' al ' . \Carbon\Carbon::parse($this->fecha_fin)->format('d/m/Y')
        ];
        $data[] = ['Generado: ' . now()->format('d/m/Y H:i')];
        $data[] = ['Usuario: ' . auth()->user()->name];
        $data[] = []; // Fila vacía

        // Encabezados de la tabla principal
        $data[] = ['Método de Pago', 'Monto Total', 'Porcentaje'];

        // Calcular porcentajes
        $total = $this->totales['total_general'];
        $porcentajeEfectivo = $total > 0 ? ($this->totales['efectivo'] / $total) * 100 : 0;
        $porcentajeTarjeta = $total > 0 ? ($this->totales['tarjeta'] / $total) * 100 : 0;
        $porcentajeTransferencia = $total > 0 ? ($this->totales['transferencia'] / $total) * 100 : 0;

        // Datos principales
        $data[] = [
            'Efectivo',
            '$' . number_format($this->totales['efectivo'], 2),
            number_format($porcentajeEfectivo, 2) . '%'
        ];
        $data[] = [
            'Tarjeta',
            '$' . number_format($this->totales['tarjeta'], 2),
            number_format($porcentajeTarjeta, 2) . '%'
        ];
        $data[] = [
            'Transferencia',
            '$' . number_format($this->totales['transferencia'], 2),
            number_format($porcentajeTransferencia, 2) . '%'
        ];

        $data[] = []; // Fila vacía

        // Fila de total
        $data[] = [
            'TOTAL GENERAL',
            '$' . number_format($this->totales['total_general'], 2),
            '100.00%'
        ];

        $data[] = []; // Fila vacía
        $data[] = []; // Fila vacía

        // Sección de Resumen
        $data[] = ['RESUMEN DEL PERÍODO'];
        $data[] = []; // Fila vacía
        $data[] = ['Concepto', 'Valor'];
        $data[] = ['Total de Reservas Procesadas', $this->totales['cantidad_reservas']];

        if ($this->totales['combinado'] > 0) {
            $data[] = [
                'Pagos Combinados (incluidos en totales)',
                '$' . number_format($this->totales['combinado'], 2)
            ];
        }

        // Promedio por reserva
        $promedio = $this->totales['cantidad_reservas'] > 0
            ? $this->totales['total_general'] / $this->totales['cantidad_reservas']
            : 0;

        $data[] = ['Promedio por Reserva', '$' . number_format($promedio, 2)];

        $data[] = []; // Fila vacía
        $data[] = []; // Fila vacía

        // Distribución detallada
        $data[] = ['DISTRIBUCIÓN DETALLADA POR MÉTODO'];
        $data[] = []; // Fila vacía
        $data[] = ['Método', 'Monto', '% del Total', 'Cantidad Estimada'];

        // Estimamos que cada método tiene una proporción similar de reservas
        $reservasPorMetodo = $this->totales['cantidad_reservas'] > 0
            ? [
                'efectivo' => round($this->totales['cantidad_reservas'] * ($porcentajeEfectivo / 100)),
                'tarjeta' => round($this->totales['cantidad_reservas'] * ($porcentajeTarjeta / 100)),
                'transferencia' => round($this->totales['cantidad_reservas'] * ($porcentajeTransferencia / 100)),
            ]
            : ['efectivo' => 0, 'tarjeta' => 0, 'transferencia' => 0];

        $data[] = [
            'Efectivo',
            '$' . number_format($this->totales['efectivo'], 2),
            number_format($porcentajeEfectivo, 2) . '%',
            $reservasPorMetodo['efectivo'] . ' reservas'
        ];
        $data[] = [
            'Tarjeta',
            '$' . number_format($this->totales['tarjeta'], 2),
            number_format($porcentajeTarjeta, 2) . '%',
            $reservasPorMetodo['tarjeta'] . ' reservas'
        ];
        $data[] = [
            'Transferencia',
            '$' . number_format($this->totales['transferencia'], 2),
            number_format($porcentajeTransferencia, 2) . '%',
            $reservasPorMetodo['transferencia'] . ' reservas'
        ];

        return $data;
    }

    /**
     * Encabezados del documento
     */
    public function headings(): array
    {
        return [];
    }

    /**
     * Aplicar estilos al documento
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo del título principal (fila 1)
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 18,
                    'color' => ['rgb' => '7c3aed'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],

            // Información del reporte (filas 2-4)
            2 => ['font' => ['italic' => true, 'size' => 10]],
            3 => ['font' => ['italic' => true, 'size' => 10]],
            4 => ['font' => ['italic' => true, 'size' => 10]],

            // Encabezado de la tabla principal (fila 6)
            6 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '7c3aed'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],

            // Fila de TOTAL GENERAL (fila 11)
            11 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
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
            ],

            // Título RESUMEN (fila 14)
            14 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => '7c3aed'],
                ],
            ],

            // Encabezado del resumen (fila 16)
            16 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '9333ea'],
                ],
            ],

            // Título DISTRIBUCIÓN DETALLADA (fila 22)
            22 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => '7c3aed'],
                ],
            ],

            // Encabezado distribución detallada (fila 24)
            24 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '9333ea'],
                ],
            ],
        ];
    }

    /**
     * Registrar eventos para aplicar estilos adicionales
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Fusionar celdas del título
                $sheet->mergeCells('A1:C1');

                // Aplicar bordes a la tabla principal (filas 6-9)
                $sheet->getStyle('A6:C9')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);

                // Aplicar bordes al resumen (filas 16-19)
                $lastResumenRow = 17;
                if ($this->totales['combinado'] > 0) {
                    $lastResumenRow = 19;
                }

                $sheet->getStyle("A16:B{$lastResumenRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);

                // Aplicar bordes a la distribución detallada (filas 24-27)
                $sheet->getStyle('A24:D27')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);

                // Alternar colores en filas de datos principales
                $sheet->getStyle('A7:C7')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F0FDF4'],
                    ],
                ]);

                $sheet->getStyle('A8:C8')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EFF6FF'],
                    ],
                ]);

                $sheet->getStyle('A9:C9')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FAF5FF'],
                    ],
                ]);

                // Alternar colores en distribución detallada
                $sheet->getStyle('A25:D25')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9FAFB'],
                    ],
                ]);

                $sheet->getStyle('A27:D27')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9FAFB'],
                    ],
                ]);

                // Centrar contenido de columnas numéricas
                $sheet->getStyle('B6:C27')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Ajustar anchos de columnas
                $sheet->getColumnDimension('A')->setWidth(35);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(20);
            },
        ];
    }

    /**
     * Título de la hoja
     */
    public function title(): string
    {
        return 'Reporte de Ingresos';
    }
}
