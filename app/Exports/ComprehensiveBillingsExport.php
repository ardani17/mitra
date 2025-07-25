<?php

namespace App\Exports;

use App\Models\ProjectBilling;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ComprehensiveBillingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $billings;
    protected $isTemplate;

    public function __construct($billings = null, $isTemplate = false)
    {
        $this->billings = $billings;
        $this->isTemplate = $isTemplate;
    }

    public function collection()
    {
        if ($this->isTemplate) {
            return collect([]);
        }

        if ($this->billings) {
            return $this->billings;
        }

        return ProjectBilling::with(['project', 'billingBatch'])->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Proyek',
            'Nama Proyek',
            'Batch Billing',
            'Nomor SP',
            'Nomor Invoice',
            'Nomor Faktur Pajak',
            'Jumlah Tagihan (Rp)',
            'Tanggal Tagihan',
            'Tanggal Jatuh Tempo',
            'Status Pembayaran',
            'Tanggal Pembayaran',
            'Tipe Klien',
            'Persentase Tagihan (%)',
            'Catatan',
            'Dibuat Tanggal',
            'Update Terakhir'
        ];
    }

    public function map($billing): array
    {
        if ($this->isTemplate) {
            return [];
        }

        static $no = 1;

        return [
            $no++,
            $billing->project->code ?? '',
            $billing->project->name ?? '',
            $billing->billingBatch->name ?? '',
            $billing->sp_number ?? '',
            $billing->invoice_number ?? '',
            $billing->tax_invoice_number ?? '',
            $billing->amount,
            $billing->billing_date ? $billing->billing_date->format('Y-m-d') : '',
            $billing->due_date ? $billing->due_date->format('Y-m-d') : '',
            $this->getStatusLabel($billing->status),
            $billing->paid_date ? $billing->paid_date->format('Y-m-d') : '',
            $this->getClientTypeLabel($billing->project->client_type ?? ''),
            $billing->percentage ?? 0,
            $billing->notes ?? '',
            $billing->created_at->format('Y-m-d H:i:s'),
            $billing->updated_at->format('Y-m-d H:i:s')
        ];
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'draft' => 'Draft',
            'sent' => 'Terkirim',
            'paid' => 'Lunas',
            'overdue' => 'Terlambat',
            'cancelled' => 'Dibatalkan'
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    private function getClientTypeLabel($clientType)
    {
        $types = [
            'pemerintah' => 'Pemerintah',
            'swasta' => 'Swasta'
        ];

        return $types[$clientType] ?? $clientType;
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
                    'startColor' => ['rgb' => '7C3AED'] // Violet-600
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '6D28D9']
                    ]
                ]
            ],
            // Data rows styling
            'A:Q' => [
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
            'H:H' => [
                'numberFormat' => [
                    'formatCode' => '#,##0'
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT
                ]
            ],
            // Percentage column formatting
            'N:N' => [
                'numberFormat' => [
                    'formatCode' => '0.00"%"'
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ]
            ]
        ];

        if ($this->isTemplate) {
            // Add instruction rows for template
            $sheet->insertNewRowBefore(2);
            $sheet->setCellValue('A2', 'PETUNJUK PENGISIAN TEMPLATE BILLING:');
            $sheet->setCellValue('A3', '1. Kolom wajib: Kode Proyek, Jumlah Tagihan, Tanggal Tagihan');
            $sheet->setCellValue('A4', '2. Status: draft, sent, paid, overdue, cancelled');
            $sheet->setCellValue('A5', '3. Tipe Klien: pemerintah, swasta');
            $sheet->setCellValue('A6', '4. Format tanggal: YYYY-MM-DD (contoh: 2025-01-15)');
            $sheet->setCellValue('A7', '5. Jumlah dalam angka tanpa titik/koma (contoh: 5000000)');
            $sheet->setCellValue('A8', '6. Persentase dalam desimal (contoh: 0.3 untuk 30%)');
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
            'A' => 5,   // No
            'B' => 15,  // Kode Proyek
            'C' => 25,  // Nama Proyek
            'D' => 20,  // Batch Billing
            'E' => 15,  // Nomor SP
            'F' => 15,  // Nomor Invoice
            'G' => 18,  // Nomor Faktur Pajak
            'H' => 18,  // Jumlah Tagihan
            'I' => 15,  // Tanggal Tagihan
            'J' => 15,  // Tanggal Jatuh Tempo
            'K' => 15,  // Status Pembayaran
            'L' => 15,  // Tanggal Pembayaran
            'M' => 12,  // Tipe Klien
            'N' => 12,  // Persentase Tagihan
            'O' => 30,  // Catatan
            'P' => 18,  // Dibuat Tanggal
            'Q' => 18   // Update Terakhir
        ];
    }
}
