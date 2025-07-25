<?php

namespace App\Exports;

use App\Models\ProjectBilling;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BillingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = ProjectBilling::with('project');

        // Apply filters
        if (!empty($this->filters['project_id'])) {
            $query->where('project_id', $this->filters['project_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('billing_date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('billing_date', '<=', $this->filters['date_to']);
        }

        return $query->orderBy('billing_date', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Tanggal Penagihan',
            'Kode Proyek',
            'Nama Proyek',
            'Tipe Klien',
            'Nomor Invoice',
            'Nomor SP',
            'Nomor Faktur Pajak',
            'Nilai Jasa',
            'Nilai Material',
            'DPP',
            'Rate PPN (%)',
            'Nilai PPN',
            'Total Amount',
            'Status',
            'Tanggal Bayar',
            'Deskripsi'
        ];
    }

    /**
     * @param mixed $billing
     * @return array
     */
    public function map($billing): array
    {
        return [
            $billing->billing_date->format('d/m/Y'),
            $billing->project->code,
            $billing->project->name,
            $billing->project->client_type_label,
            $billing->invoice_number,
            $billing->sp_number,
            $billing->tax_invoice_number,
            $billing->nilai_jasa,
            $billing->nilai_material,
            $billing->nilai_jasa + $billing->nilai_material,
            $billing->ppn_rate,
            $billing->ppn_amount,
            $billing->total_amount,
            $billing->status_label,
            $billing->paid_date ? $billing->paid_date->format('d/m/Y') : '',
            $billing->description
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
