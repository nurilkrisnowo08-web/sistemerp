<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class POTemplateExport implements WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    /**
     * Judul Kolom (Baris 1)
     */
    public function headings(): array
    {
        return [
            'po_number',
            'customer_code',
            'part_no',
            'quantity',
            'due_date'
        ];
    }

    /**
     * Nama Sheet di Excel
     */
    public function title(): string
    {
        return 'Template Import PO';
    }

    /**
     * Styling biar makin Pro
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style Baris 1 (Header)
            1 => [
                'font' => [
                    'bold' => true, 
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4E73DF'] // Biru ASALTA
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            
            // Tambahkan border ke area data (misal sampai baris 100)
            'A1:E100' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
        ];
    }
}