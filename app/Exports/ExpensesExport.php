<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $expenses;

    public function __construct($expenses)
    {
        $this->expenses = $expenses;
    }

    public function collection()
    {
        return $this->expenses;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Proyek',
            'Nama Proyek',
            'Deskripsi',
            'Kategori',
            'Jumlah (Rp)',
            'Tanggal Pengeluaran',
            'Nomor Kwitansi',
            'Status',
            'Dibuat Oleh',
            'Tanggal Dibuat',
            'Status Approval Finance',
            'Status Approval Manager',
            'Catatan Approval'
        ];
    }

    public function map($expense): array
    {
        static $no = 1;
        
        // Get approval status
        $financeApproval = $expense->approvals->where('level', 'finance_manager')->first();
        $managerApproval = $expense->approvals->whereIn('level', ['direktur', 'project_manager'])->first();
        
        $financeStatus = $financeApproval ? ucfirst($financeApproval->status) : 'Pending';
        $managerStatus = $managerApproval ? ucfirst($managerApproval->status) : 'Pending';
        
        $notes = '';
        if ($financeApproval && $financeApproval->notes) {
            $notes .= 'Finance: ' . $financeApproval->notes . ' ';
        }
        if ($managerApproval && $managerApproval->notes) {
            $notes .= 'Manager: ' . $managerApproval->notes;
        }

        return [
            $no++,
            $expense->project->code ?? '',
            $expense->project->name ?? '',
            $expense->description,
            $expense->category ?? '',
            $expense->amount,
            $expense->expense_date ? $expense->expense_date->format('d/m/Y') : '',
            $expense->receipt_number ?? '',
            $this->getStatusLabel($expense->status),
            'System',
            $expense->created_at->format('d/m/Y H:i'),
            $financeStatus,
            $managerStatus,
            trim($notes)
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

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'] // Blue-600
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            // Data rows styling
            'A:N' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true
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
            'K' => 15,  // Tanggal Dibuat
            'L' => 15,  // Status Approval Finance
            'M' => 15,  // Status Approval Manager
            'N' => 30   // Catatan Approval
        ];
    }
}
