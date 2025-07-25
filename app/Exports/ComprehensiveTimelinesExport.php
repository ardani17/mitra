<?php

namespace App\Exports;

use App\Models\ProjectTimeline;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ComprehensiveTimelinesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $timelines;
    protected $isTemplate;

    public function __construct($timelines = null, $isTemplate = false)
    {
        $this->timelines = $timelines;
        $this->isTemplate = $isTemplate;
    }

    public function collection()
    {
        if ($this->isTemplate) {
            return collect([]);
        }

        if ($this->timelines) {
            return $this->timelines;
        }

        return ProjectTimeline::with(['project'])->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Proyek',
            'Nama Proyek',
            'Milestone',
            'Deskripsi',
            'Tanggal Rencana',
            'Tanggal Aktual',
            'Status',
            'Progress (%)',
            'Selisih Hari',
            'Keterangan Keterlambatan',
            'Dibuat Tanggal',
            'Update Terakhir'
        ];
    }

    public function map($timeline): array
    {
        if ($this->isTemplate) {
            return [];
        }

        static $no = 1;

        // Calculate day difference
        $dayDifference = '';
        $delayNote = '';
        
        if ($timeline->planned_date && $timeline->actual_date) {
            $plannedDate = $timeline->planned_date;
            $actualDate = $timeline->actual_date;
            $diff = $actualDate->diffInDays($plannedDate, false);
            
            if ($diff > 0) {
                $dayDifference = "+{$diff} hari";
                $delayNote = "Selesai {$diff} hari lebih cepat";
            } elseif ($diff < 0) {
                $dayDifference = abs($diff) . " hari";
                $delayNote = "Terlambat " . abs($diff) . " hari";
            } else {
                $dayDifference = "Tepat waktu";
                $delayNote = "Selesai tepat waktu";
            }
        } elseif ($timeline->planned_date && !$timeline->actual_date && $timeline->status !== 'completed') {
            $today = now();
            $plannedDate = $timeline->planned_date;
            
            if ($today->gt($plannedDate)) {
                $diff = $today->diffInDays($plannedDate);
                $dayDifference = "{$diff} hari";
                $delayNote = "Terlambat {$diff} hari dari rencana";
            }
        }

        return [
            $no++,
            $timeline->project->code ?? '',
            $timeline->project->name ?? '',
            $timeline->milestone,
            $timeline->description ?? '',
            $timeline->planned_date ? $timeline->planned_date->format('Y-m-d') : '',
            $timeline->actual_date ? $timeline->actual_date->format('Y-m-d') : '',
            $this->getStatusLabel($timeline->status),
            $timeline->progress_percentage ?? 0,
            $dayDifference,
            $delayNote,
            $timeline->created_at->format('Y-m-d H:i:s'),
            $timeline->updated_at->format('Y-m-d H:i:s')
        ];
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'planned' => 'Direncanakan',
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'delayed' => 'Terlambat',
            'cancelled' => 'Dibatalkan'
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F59E0B'] // Amber-500
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D97706']
                    ]
                ]
            ],
            // Data rows styling
            'A:M' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB']
                    ]
                ]
            ],
            // Progress column formatting
            'I:I' => [
                'numberFormat' => [
                    'formatCode' => '0"%"'
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ]
            ]
        ];

        if ($this->isTemplate) {
            // Add instruction rows for template
            $sheet->insertNewRowBefore(2);
            $sheet->setCellValue('A2', 'PETUNJUK PENGISIAN TEMPLATE TIMELINE:');
            $sheet->setCellValue('A3', '1. Kolom wajib: Kode Proyek, Milestone, Tanggal Rencana');
            $sheet->setCellValue('A4', '2. Status: planned, in_progress, completed, delayed, cancelled');
            $sheet->setCellValue('A5', '3. Format tanggal: YYYY-MM-DD (contoh: 2025-01-15)');
            $sheet->setCellValue('A6', '4. Progress dalam angka 0-100 (contoh: 75 untuk 75%)');
            $sheet->setCellValue('A7', '5. Tanggal Aktual diisi jika milestone sudah selesai');
            $sheet->setCellValue('A8', '6. Hapus baris petunjuk ini sebelum import');
            $sheet->setCellValue('A9', '');
            
            $styles[2] = ['font' => ['bold' => true, 'color' => ['rgb' => 'DC2626']]];
            $styles['A3:A8'] = ['font' => ['italic' => true, 'color' => ['rgb' => '6B7280']]];
        }

        return $styles;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 15,  // Kode Proyek
            'C' => 25,  // Nama Proyek
            'D' => 25,  // Milestone
            'E' => 30,  // Deskripsi
            'F' => 15,  // Tanggal Rencana
            'G' => 15,  // Tanggal Aktual
            'H' => 15,  // Status
            'I' => 12,  // Progress
            'J' => 15,  // Selisih Hari
            'K' => 25,  // Keterangan Keterlambatan
            'L' => 18,  // Dibuat Tanggal
            'M' => 18   // Update Terakhir
        ];
    }
}
