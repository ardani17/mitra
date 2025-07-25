<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ProjectTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithColumnWidths
{
    /**
     * @return array
     */
    public function array(): array
    {
        // Template dengan contoh data
        return [
            [
                'Proyek Fiber Optic Jakarta Selatan',
                'Instalasi fiber optic untuk area Jakarta Selatan',
                'fiber_optic',
                'planning',
                'high',
                'Jakarta Selatan',
                50000000,
                30000000,
                '2025-08-01',
                '2025-09-30',
                'Proyek prioritas tinggi untuk Q3 2025'
            ],
            [
                'Maintenance Tower Bekasi',
                'Maintenance rutin tower telekomunikasi di Bekasi',
                'maintenance',
                'draft',
                'medium',
                'Bekasi',
                15000000,
                5000000,
                '2025-08-15',
                '2025-08-20',
                'Maintenance bulanan'
            ]
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'nama_proyek',
            'deskripsi',
            'tipe',
            'status',
            'prioritas',
            'lokasi',
            'nilai_jasa_plan',
            'nilai_material_plan',
            'tanggal_mulai',
            'tanggal_selesai',
            'catatan'
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 40,
            'C' => 20,
            'D' => 15,
            'E' => 15,
            'F' => 20,
            'G' => 20,
            'H' => 20,
            'I' => 15,
            'J' => 15,
            'K' => 30,
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Add instructions in the first few rows
        $sheet->insertNewRowBefore(1, 5);
        
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT PROYEK TELEKOMUNIKASI');
        $sheet->setCellValue('A2', 'Petunjuk Penggunaan:');
        $sheet->setCellValue('A3', '1. Isi data proyek mulai dari baris 7 (setelah header)');
        $sheet->setCellValue('A4', '2. Jangan mengubah nama kolom di baris 6');
        $sheet->setCellValue('A5', '3. Pastikan format tanggal: YYYY-MM-DD (contoh: 2025-08-01)');
        
        // Add validation info
        $sheet->setCellValue('M1', 'NILAI YANG DIIZINKAN:');
        $sheet->setCellValue('M2', 'Tipe: fiber_optic, tower_installation, maintenance, upgrade, other');
        $sheet->setCellValue('M3', 'Status: draft, planning, in_progress, on_hold, completed, cancelled');
        $sheet->setCellValue('M4', 'Prioritas: low, medium, high, urgent');
        $sheet->setCellValue('M5', 'Nilai dalam Rupiah (tanpa titik/koma)');

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4472C4']
                ]
            ],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['italic' => true]],
            4 => ['font' => ['italic' => true]],
            5 => ['font' => ['italic' => true]],
            6 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE7E6E6']
                ]
            ],
            'M1:M5' => [
                'font' => ['bold' => true, 'size' => 10],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFFF2CC']
                ]
            ]
        ];
    }
}
