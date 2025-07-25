<?php

namespace App\Exports;

use App\Models\ProjectExpense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ComprehensiveExpensesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $expenses;
    protected $isTemplate;

    public function __construct($expenses = null, $isTemplate = false)
    {
        $this->expenses = $expenses;
        $this->isTemplate = $isTemplate;
    }

    public function collection()
    {
        if ($this->isTemplate) {
            return collect([]);
        }

        if ($this->expenses) {
            return $this->expenses;
        }

        return ProjectExpense::with(['project', 'approvals.user'])->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Proyek',
            'Nama Proyek',
            'Deskripsi Pengeluaran',
            'Kategori',
            'Jumlah (Rp)',
            'Tanggal Pengeluaran',
            'Nomor Kwitansi',
            'Status',
            'Dibuat Oleh',
            'Tanggal Dibuat',
            'Status Approval Finance',
            'Tanggal Approval Finance',
            'Catatan Finance',
            'Status Approval Manager',
            'Tanggal Approval Manager',
            'Catatan Manager',
            'Approval Terakhir Oleh',
            'Tanggal Update Terakhir',
            'Catatan Tambahan'
        ];
    }

    public function map($expense): array
    {
        if ($this->isTemplate) {
            return [];
        }

        static $no = 1;
        
        // Get approval status
        $financeApproval = $expense->approvals->where('level', 'finance_manager')->first();
        $managerApproval = $expense->approvals->whereIn('level', ['direktur', 'project_manager'])->first();
        
        $financeStatus = $financeApproval ? $this->getApprovalStatusLabel($financeApproval->status) : 'Pending';
        $managerStatus = $managerApproval ? $this->getApprovalStatusLabel($managerApproval->status) : 'Pending';
        
        $lastApproval = $expense->approvals->sortByDesc('created_at')->first();

        return [
            $no++,
            $expense->project->code ?? '',
            $expense->project->name ?? '',
            $expense->description,
            $expense->category ?? '',
            $expense->amount,
            $expense->expense_date ? $expense->expense_date->format('Y-m-d') : '',
            $expense->receipt_number ?? '',
            $this->getStatusLabel($expense->status),
            'System',
            $expense->created_at->format('Y-m-d H:i:s'),
            $financeStatus,
            $financeApproval && $financeApproval->created_at ? $financeApproval->created_at->format('Y-m-d H:i:s') : '',
            $financeApproval->notes ?? '',
            $managerStatus,
            $managerApproval && $managerApproval->created_at ? $managerApproval->created_at->format('Y-m-d H:i:s') : '',
            $managerApproval->notes ?? '',
            $lastApproval && $lastApproval->user ? $lastApproval->user->name : '',
            $expense->updated_at->format('Y-m-d H:i:s'),
            $expense->notes ?? ''
        ];
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'draft' => 'Draft',
            'submitted' => 'Diajukan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak'
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    private function getApprovalStatusLabel($status)
    {
        $labels = [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak'
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
                    'startColor' => ['rgb' => '059669'] // Emerald-600
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '047857']
                    ]
                ]
            ],
            // Data rows styling
            'A:T' => [
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
            // Amount column formatting
            'F:F' => [
                'numberFormat' => [
                    'formatCode' => '#,##0'
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT
                ]
            ]
        ];

        if ($this->isTemplate) {
            // Add instruction rows for template
            $sheet->insertNewRowBefore(2);
            $sheet->setCellValue('A2', 'PETUNJUK PENGISIAN TEMPLATE PENGELUARAN:');
            $sheet->setCellValue('A3', '1. Kolom yang wajib diisi: Kode Proyek, Deskripsi, Jumlah, Tanggal Pengeluaran');
            $sheet->setCellValue('A4', '2. Kategori: material, tenaga_kerja, transportasi, konsumsi, lainnya');
            $sheet->setCellValue('A5', '3. Status: draft, submitted (untuk pengajuan baru)');
            $sheet->setCellValue('A6', '4. Format tanggal: YYYY-MM-DD (contoh: 2025-01-15)');
            $sheet->setCellValue('A7', '5. Jumlah dalam angka tanpa titik/koma (contoh: 500000)');
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
            'D' => 30,  // Deskripsi
            'E' => 15,  // Kategori
            'F' => 15,  // Jumlah
            'G' => 15,  // Tanggal Pengeluaran
            'H' => 15,  // Nomor Kwitansi
            'I' => 12,  // Status
            'J' => 20,  // Dibuat Oleh
            'K' => 18,  // Tanggal Dibuat
            'L' => 15,  // Status Approval Finance
            'M' => 18,  // Tanggal Approval Finance
            'N' => 25,  // Catatan Finance
            'O' => 15,  // Status Approval Manager
            'P' => 18,  // Tanggal Approval Manager
            'Q' => 25,  // Catatan Manager
            'R' => 20,  // Approval Terakhir Oleh
            'S' => 18,  // Tanggal Update Terakhir
            'T' => 30   // Catatan Tambahan
        ];
    }
}
