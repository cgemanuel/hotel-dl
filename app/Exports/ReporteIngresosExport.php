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

class ReporteIngresosExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $totales;
    protected $fecha_inicio;
    protected $fecha_fin;

    public function __construct($totales, $fecha_inicio, $fecha_fin)
    {
        $this->totales      = $totales;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin    = $fecha_fin;
    }

    // ─────────────────────────────────────────────────────
    // MAPA DE FILAS (para mantener estilos sincronizados)
    //
    //  1  → Título principal
    //  2  → Período
    //  3  → Generado
    //  4  → Usuario
    //  5  → (vacía)
    //  6  → Encabezado tabla: Método | Monto | %
    //  7  → Efectivo
    //  8  → Tarjeta de Débito        ← nuevo
    //  9  → Tarjeta de Crédito       ← nuevo
    // 10  → Transferencia
    // 11  → (vacía)
    // 12  → TOTAL GENERAL
    // 13  → (vacía)
    // 14  → (vacía)
    // 15  → RESUMEN DEL PERÍODO
    // 16  → (vacía)
    // 17  → Encabezado resumen: Concepto | Valor
    // 18  → Total de Reservas Procesadas
    // 19  → Pagos Combinados (solo si > 0)  ← condicional
    // 20  → Promedio por Reserva            ← +1 si hay combinado
    // 21  → (vacía)
    // 22  → (vacía)
    // 23  → DISTRIBUCIÓN DETALLADA
    // 24  → (vacía)
    // 25  → Encabezado detalle: Método | Monto | % | Cantidad
    // 26  → Efectivo detalle
    // 27  → T. Débito detalle
    // 28  → T. Crédito detalle
    // 29  → Transferencia detalle
    // ─────────────────────────────────────────────────────

    public function array(): array
    {
        $data  = [];
        $total = $this->totales['total_general'];

        // ── Porcentajes ──
        $pEfectivo   = $total > 0 ? ($this->totales['efectivo']        / $total) * 100 : 0;
        $pDebito     = $total > 0 ? ($this->totales['tarjeta_debito']  / $total) * 100 : 0;
        $pCredito    = $total > 0 ? ($this->totales['tarjeta_credito'] / $total) * 100 : 0;
        $pTransfer   = $total > 0 ? ($this->totales['transferencia']   / $total) * 100 : 0;

        // ── CABECERA ── (filas 1-5)
        $data[] = ['Hotel Don Luis - Reporte de Ingresos por Método de Pago'];
        $data[] = ['Período: ' . \Carbon\Carbon::parse($this->fecha_inicio)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($this->fecha_fin)->format('d/m/Y')];
        $data[] = ['Generado: ' . now()->format('d/m/Y H:i')];
        $data[] = ['Usuario: ' . auth()->user()->name];
        $data[] = []; // fila 5 vacía

        // ── TABLA PRINCIPAL ── (filas 6-12)
        $data[] = ['Método de Pago', 'Monto Total', 'Porcentaje'];                       // fila 6
        $data[] = ['Efectivo',           '$' . number_format($this->totales['efectivo'],        2), number_format($pEfectivo, 2) . '%']; // 7
        $data[] = ['Tarjeta de Débito',  '$' . number_format($this->totales['tarjeta_debito'],  2), number_format($pDebito,   2) . '%']; // 8
        $data[] = ['Tarjeta de Crédito', '$' . number_format($this->totales['tarjeta_credito'], 2), number_format($pCredito,  2) . '%']; // 9
        $data[] = ['Transferencia',      '$' . number_format($this->totales['transferencia'],   2), number_format($pTransfer, 2) . '%']; // 10
        $data[] = []; // fila 11 vacía
        $data[] = ['TOTAL GENERAL', '$' . number_format($total, 2), '100.00%'];           // fila 12
        $data[] = []; // fila 13
        $data[] = []; // fila 14

        // ── RESUMEN ── (filas 15-20+)
        $data[] = ['RESUMEN DEL PERÍODO'];   // fila 15
        $data[] = [];                        // fila 16
        $data[] = ['Concepto', 'Valor'];     // fila 17
        $data[] = ['Total de Reservas Procesadas', $this->totales['cantidad_reservas']]; // fila 18

        $filaPromedio = 19;
        if ($this->totales['combinado'] > 0) {
            $data[] = ['Pagos Combinados (incluidos en totales)', '$' . number_format($this->totales['combinado'], 2)]; // fila 19
            $filaPromedio = 20;
        }

        $promedio = $this->totales['cantidad_reservas'] > 0
            ? $total / $this->totales['cantidad_reservas']
            : 0;
        $data[] = ['Promedio por Reserva', '$' . number_format($promedio, 2)]; // fila 19 o 20
        $data[] = []; // vacía
        $data[] = []; // vacía

        // ── DISTRIBUCIÓN DETALLADA ──
        $filaDistTitulo  = $filaPromedio + 3;   // 22 o 23
        $filaDistVacia   = $filaDistTitulo + 1;
        $filaDistHeader  = $filaDistVacia + 1;  // 24 o 25
        $filaDistData    = $filaDistHeader + 1; // 25 o 26

        $data[] = ['DISTRIBUCIÓN DETALLADA POR MÉTODO'];                         // fila dinámica
        $data[] = [];
        $data[] = ['Método', 'Monto', '% del Total', 'Cantidad Estimada'];

        $cant = $this->totales['cantidad_reservas'];
        $data[] = ['Efectivo',           '$' . number_format($this->totales['efectivo'],        2), number_format($pEfectivo, 2) . '%', round($cant * $pEfectivo  / 100) . ' reservas'];
        $data[] = ['Tarjeta de Débito',  '$' . number_format($this->totales['tarjeta_debito'],  2), number_format($pDebito,   2) . '%', round($cant * $pDebito    / 100) . ' reservas'];
        $data[] = ['Tarjeta de Crédito', '$' . number_format($this->totales['tarjeta_credito'], 2), number_format($pCredito,  2) . '%', round($cant * $pCredito   / 100) . ' reservas'];
        $data[] = ['Transferencia',      '$' . number_format($this->totales['transferencia'],   2), number_format($pTransfer, 2) . '%', round($cant * $pTransfer  / 100) . ' reservas'];

        // Guardamos referencias de filas para usarlas en estilos/eventos
        $this->filaDistTitulo = $filaDistTitulo;
        $this->filaDistHeader = $filaDistHeader;
        $this->filaDistData   = $filaDistData;
        $this->filaPromedio   = $filaPromedio;

        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Título principal
            1 => [
                'font'      => ['bold' => true, 'size' => 18, 'color' => ['rgb' => '7c3aed']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Info del reporte
            2 => ['font' => ['italic' => true, 'size' => 10]],
            3 => ['font' => ['italic' => true, 'size' => 10]],
            4 => ['font' => ['italic' => true, 'size' => 10]],
            // Encabezado tabla principal (fila 6)
            6 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7c3aed']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // TOTAL GENERAL (fila 12)
            12 => [
                'font'    => ['bold' => true, 'size' => 14],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']]],
            ],
            // Título RESUMEN (fila 15)
            15 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '7c3aed']],
            ],
            // Encabezado resumen (fila 17)
            17 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9333ea']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Fusionar título
                $sheet->mergeCells('A1:C1');

                // Bordes tabla principal (filas 6-10: encabezado + 4 métodos)
                $sheet->getStyle('A6:C10')->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
                ]);

                // Colores alternos filas de datos principales
                // fila 7  Efectivo        → verde
                $sheet->getStyle('A7:C7')->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FDF4']]]);
                // fila 8  T. Débito       → azul claro
                $sheet->getStyle('A8:C8')->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']]]);
                // fila 9  T. Crédito      → índigo claro
                $sheet->getStyle('A9:C9')->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2FF']]]);
                // fila 10 Transferencia   → violeta claro
                $sheet->getStyle('A10:C10')->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FAF5FF']]]);

                // Encabezado RESUMEN (fila 17)
                $sheet->getStyle('A17:B17')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9333ea']],
                ]);

                // Bordes resumen
                $lastResumen = isset($this->filaPromedio) ? $this->filaPromedio : 19;
                $sheet->getStyle("A17:B{$lastResumen}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
                ]);

                // Título DISTRIBUCIÓN DETALLADA
                $filaDistTitulo = isset($this->filaDistTitulo) ? $this->filaDistTitulo : 23;
                $filaDistHeader = isset($this->filaDistHeader) ? $this->filaDistHeader : 25;
                $filaDistData   = isset($this->filaDistData)   ? $this->filaDistData   : 26;

                $sheet->getStyle("A{$filaDistTitulo}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '7c3aed']],
                ]);

                // Encabezado distribución
                $sheet->getStyle("A{$filaDistHeader}:D{$filaDistHeader}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9333ea']],
                ]);

                // Bordes distribución (4 filas de datos)
                $lastDist = $filaDistData + 3;
                $sheet->getStyle("A{$filaDistHeader}:D{$lastDist}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
                ]);

                // Colores alternos distribución detallada
                $sheet->getStyle("A{$filaDistData}:D{$filaDistData}")->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FDF4']]]);
                $r1 = $filaDistData + 1;
                $sheet->getStyle("A{$r1}:D{$r1}")->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']]]);
                $r2 = $filaDistData + 2;
                $sheet->getStyle("A{$r2}:D{$r2}")->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2FF']]]);
                $r3 = $filaDistData + 3;
                $sheet->getStyle("A{$r3}:D{$r3}")->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FAF5FF']]]);

                // Centrar columnas numéricas
                $sheet->getStyle("B6:C{$lastDist}")->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Anchos de columna
                $sheet->getColumnDimension('A')->setWidth(35);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(22);
            },
        ];
    }

    public function title(): string
    {
        return 'Reporte de Ingresos';
    }
}
