<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialReportExport implements WithMultipleSheets
{
    protected $projects;
    protected $startDate;
    protected $endDate;

    public function __construct($projects, $startDate, $endDate)
    {
        $this->projects = $projects;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new ProjectSummarySheet($this->projects, $this->startDate, $this->endDate);
        $sheets[] = new ExpenseDetailSheet($this->projects, $this->startDate, $this->endDate);
        $sheets[] = new RevenueDetailSheet($this->projects, $this->startDate, $this->endDate);

        return $sheets;
    }
}

class ProjectSummarySheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $projects;
    protected $startDate;
    protected $endDate;

    public function __construct($projects, $startDate, $endDate)
    {
        $this->projects = $projects;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return $this->projects;
    }

    public function headings(): array
    {
        return [
            'Kode Proyek',
            'Nama Proyek',
            'Status',
            'Nilai Plan (Rp)',
            'Nilai Akhir (Rp)',
            'Total Pendapatan (Rp)',
            'Total Pengeluaran (Rp)',
            'Net Profit (Rp)',
            'Profit Margin (%)',
        ];
    }

    public function map($project): array
    {
        $totalRevenue = $project->revenues->sum('amount');
        $totalExpenses = $project->expenses->sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        return [
            $project->code,
            $project->name,
            $this->getStatusLabel($project->status),
            $project->planned_total_value ?? 0,
            $project->final_total_value ?? 0,
            $totalRevenue,
            $totalExpenses,
            $netProfit,
            number_format($profitMargin, 2),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
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
}

class ExpenseDetailSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $projects;
    protected $startDate;
    protected $endDate;

    public function __construct($projects, $startDate, $endDate)
    {
        $this->projects = $projects;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $expenses = collect();
        
        foreach ($this->projects as $project) {
            foreach ($project->expenses as $expense) {
                $expenses->push($expense);
            }
        }
        
        return $expenses;
    }

    public function headings(): array
    {
        return [
            'Kode Proyek',
            'Nama Proyek',
            'Kategori Pengeluaran',
            'Deskripsi',
            'Jumlah (Rp)',
            'Tanggal',
            'Status',
            'Disetujui Oleh',
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->project->code ?? '',
            $expense->project->name ?? '',
            $expense->category,
            $expense->description,
            $expense->amount,
            $expense->expense_date->format('Y-m-d'),
            $this->getStatusLabel($expense->status),
            $expense->approvedBy->name ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function getStatusLabel($status)
    {
        $statuses = [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak'
        ];

        return $statuses[$status] ?? $status;
    }
}

class RevenueDetailSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $projects;
    protected $startDate;
    protected $endDate;

    public function __construct($projects, $startDate, $endDate)
    {
        $this->projects = $projects;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $revenues = collect();
        
        foreach ($this->projects as $project) {
            foreach ($project->revenues as $revenue) {
                $revenues->push($revenue);
            }
        }
        
        return $revenues;
    }

    public function headings(): array
    {
        return [
            'Kode Proyek',
            'Nama Proyek',
            'Sumber Pendapatan',
            'Deskripsi',
            'Jumlah (Rp)',
            'Tanggal',
            'Status',
        ];
    }

    public function map($revenue): array
    {
        return [
            $revenue->project->code ?? '',
            $revenue->project->name ?? '',
            $revenue->source,
            $revenue->description,
            $revenue->amount,
            $revenue->revenue_date->format('Y-m-d'),
            $revenue->status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
