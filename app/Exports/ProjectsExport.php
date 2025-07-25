<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProjectsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $projects;

    public function __construct($projects = null)
    {
        $this->projects = $projects;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
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
        return [
            'Kode Proyek',
            'Nama Proyek',
            'Deskripsi',
            'Tipe',
            'Status',
            'Prioritas',
            'Lokasi',
            'Nilai Jasa Plan (Rp)',
            'Nilai Material Plan (Rp)',
            'Total Nilai Plan (Rp)',
            'Nilai Jasa Akhir (Rp)',
            'Nilai Material Akhir (Rp)',
            'Total Nilai Akhir (Rp)',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Dibuat Oleh',
            'Tanggal Dibuat',
            'Catatan'
        ];
    }

    /**
     * @param mixed $project
     * @return array
     */
    public function map($project): array
    {
        return [
            $project->code,
            $project->name,
            $project->description,
            $this->getTypeLabel($project->type),
            $this->getStatusLabel($project->status),
            $this->getPriorityLabel($project->priority),
            $project->location,
            $project->planned_service_value ?? 0,
            $project->planned_material_value ?? 0,
            $project->planned_total_value ?? 0,
            $project->final_service_value ?? 0,
            $project->final_material_value ?? 0,
            $project->final_total_value ?? 0,
            $project->start_date ? $project->start_date->format('Y-m-d') : '',
            $project->end_date ? $project->end_date->format('Y-m-d') : '',
            'System',
            $project->created_at->format('Y-m-d H:i:s'),
            $project->notes
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

    private function getTypeLabel($type)
    {
        $types = [
            'konstruksi' => 'Konstruksi',
            'maintenance' => 'Maintenance',
            'other' => 'Other'
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
}
