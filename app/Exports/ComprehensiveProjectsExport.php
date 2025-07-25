<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ComprehensiveProjectsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnWidths
{
    protected $projects;
    protected $isTemplate;

    public function __construct($projects = null, $isTemplate = false)
    {
        $this->projects = $projects;
        $this->isTemplate = $isTemplate;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        if ($this->isTemplate) {
            // Return empty collection for template
            return collect([]);
        }

        if ($this->projects) {
            return $this->projects;
        }

        return Project::all();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        if ($this->isTemplate) {
            // Template headers for import (simplified)
            return [
                'nama_proyek',
                'deskripsi',
                'tipe',
                'status',
                'prioritas',
                'lokasi',
                'tipe_klien',
                'nilai_jasa_plan',
                'nilai_material_plan',
                'nilai_jasa_akhir',
                'nilai_material_akhir',
                'tanggal_mulai',
                'tanggal_selesai',
                'progress',
                'catatan'
            ];
        }
        
        // Full export headers
        return [
            'Kode Proyek',
            'Nama Proyek',
            'Deskripsi',
            'Tipe Proyek',
            'Status',
            'Prioritas',
            'Lokasi',
            'Tipe Klien',
            'Nilai Jasa Plan (Rp)',
            'Nilai Material Plan (Rp)',
            'Total Nilai Plan (Rp)',
            'Nilai Jasa Akhir (Rp)',
            'Nilai Material Akhir (Rp)',
            'Total Nilai Akhir (Rp)',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Progress (%)',
            'Dibuat Oleh',
            'Tanggal Dibuat',
            'Catatan',
            // Billing fields
            'Nomor SP',
            'Nomor Invoice',
            'Nomor Faktur Pajak',
            'Tanggal Invoice',
            'Nilai Invoice (Rp)',
            'Status Pembayaran'
        ];
    }

    /**
     * @param mixed $project
     * @return array
     */
    public function map($project): array
    {
        if ($this->isTemplate) {
            return [];
        }

        // Get latest billing info
        $latestBilling = $project->billings()->latest()->first();

        return [
            $project->code,
            $project->name,
            $project->description,
            $this->getTypeLabel($project->type),
            $this->getStatusLabel($project->status),
            $this->getPriorityLabel($project->priority),
            $project->location,
            $this->getClientTypeLabel($project->client_type),
            $project->planned_service_value ?? 0,
            $project->planned_material_value ?? 0,
            $project->planned_total_value ?? 0,
            $project->final_service_value ?? 0,
            $project->final_material_value ?? 0,
            $project->final_total_value ?? 0,
            $project->start_date ? $project->start_date->format('Y-m-d') : '',
            $project->end_date ? $project->end_date->format('Y-m-d') : '',
            $project->progress_percentage ?? 0,
            'System',
            $project->created_at->format('Y-m-d H:i:s'),
            $project->notes,
            // Billing info
            $latestBilling->sp_number ?? '',
            $latestBilling->invoice_number ?? '',
            $latestBilling->tax_invoice_number ?? '',
            $latestBilling && $latestBilling->billing_date ? $latestBilling->billing_date->format('Y-m-d') : '',
            $latestBilling->amount ?? 0,
            $latestBilling ? $this->getBillingStatusLabel($latestBilling->status) : ''
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $styles = [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F2937'] // Gray-800
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '374151']
                    ]
                ]
            ],
            // Data rows styling
            'A:Z' => [
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
            // Currency columns formatting
            'I:N' => [
                'numberFormat' => [
                    'formatCode' => '#,##0'
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT
                ]
            ],
            'Y:Y' => [
                'numberFormat' => [
                    'formatCode' => '#,##0'
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT
                ]
            ]
        ];

        if ($this->isTemplate) {
            // Add instruction row for template
            $sheet->insertNewRowBefore(2);
            $sheet->setCellValue('A2', 'PETUNJUK PENGISIAN:');
            $sheet->setCellValue('A3', '1. Isi data sesuai dengan kolom yang tersedia');
            $sheet->setCellValue('A4', '2. Tipe Proyek: konstruksi, maintenance, other');
            $sheet->setCellValue('A5', '3. Status: planning, in_progress, completed, cancelled');
            $sheet->setCellValue('A6', '4. Prioritas: low, medium, high, urgent');
            $sheet->setCellValue('A7', '5. Tipe Klien: pemerintah, swasta');
            $sheet->setCellValue('A8', '6. Format tanggal: YYYY-MM-DD (contoh: 2025-01-15)');
            $sheet->setCellValue('A9', '7. Hapus baris petunjuk ini sebelum import');
            $sheet->setCellValue('A10', '');
            
            $styles[2] = ['font' => ['bold' => true, 'color' => ['rgb' => 'DC2626']]];
            $styles['A3:A9'] = ['font' => ['italic' => true, 'color' => ['rgb' => '6B7280']]];
        }

        return $styles;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Kode Proyek
            'B' => 25,  // Nama Proyek
            'C' => 30,  // Deskripsi
            'D' => 15,  // Tipe Proyek
            'E' => 15,  // Status
            'F' => 12,  // Prioritas
            'G' => 20,  // Lokasi
            'H' => 12,  // Tipe Klien
            'I' => 18,  // Nilai Jasa Plan
            'J' => 18,  // Nilai Material Plan
            'K' => 18,  // Total Nilai Plan
            'L' => 18,  // Nilai Jasa Akhir
            'M' => 18,  // Nilai Material Akhir
            'N' => 18,  // Total Nilai Akhir
            'O' => 15,  // Tanggal Mulai
            'P' => 15,  // Tanggal Selesai
            'Q' => 12,  // Progress
            'R' => 20,  // Dibuat Oleh
            'S' => 18,  // Tanggal Dibuat
            'T' => 30,  // Catatan
            'U' => 15,  // Nomor SP
            'V' => 15,  // Nomor Invoice
            'W' => 18,  // Nomor Faktur Pajak
            'X' => 15,  // Tanggal Invoice
            'Y' => 18,  // Nilai Invoice
            'Z' => 15   // Status Pembayaran
        ];
    }

    private function getTypeLabel($type)
    {
        $types = [
            'konstruksi' => 'Konstruksi',
            'maintenance' => 'Maintenance',
            'other' => 'Lainnya'
        ];

        return $types[$type] ?? $type;
    }

    private function getStatusLabel($status)
    {
        $statuses = [
            'planning' => 'Perencanaan',
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];

        return $statuses[$status] ?? $status;
    }

    private function getPriorityLabel($priority)
    {
        $priorities = [
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            'urgent' => 'Mendesak'
        ];

        return $priorities[$priority] ?? $priority;
    }

    private function getClientTypeLabel($clientType)
    {
        $types = [
            'pemerintah' => 'Pemerintah',
            'swasta' => 'Swasta'
        ];

        return $types[$clientType] ?? $clientType;
    }

    private function getBillingStatusLabel($status)
    {
        $statuses = [
            'draft' => 'Draft',
            'sent' => 'Terkirim',
            'paid' => 'Lunas',
            'overdue' => 'Terlambat'
        ];

        return $statuses[$status] ?? $status;
    }
}
