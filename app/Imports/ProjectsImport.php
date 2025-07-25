<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class ProjectsImport implements ToCollection, WithHeadingRow
{
    protected $errors = [];
    protected $successCount = 0;
    protected $errorCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $this->validateRow($row, $index + 2); // +2 karena header di row 1 dan index dimulai dari 0
                
                if (empty($this->errors)) {
                    $this->createProject($row);
                    $this->successCount++;
                }
            } catch (\Exception $e) {
                $this->errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                $this->errorCount++;
            }
        }
    }

    protected function validateRow($row, $rowNumber)
    {
        $validator = Validator::make($row->toArray(), [
            'nama_proyek' => 'required|string|max:255',
            'tipe' => 'required|in:fiber_optic,tower_installation,maintenance,upgrade,other',
            'status' => 'required|in:draft,planning,in_progress,on_hold,completed,cancelled',
            'prioritas' => 'nullable|in:low,medium,high,urgent',
            'nilai_jasa_plan' => 'nullable|numeric|min:0',
            'nilai_material_plan' => 'nullable|numeric|min:0',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->errors[] = "Row {$rowNumber}: {$error}";
            }
            $this->errorCount++;
        }
    }

    protected function createProject($row)
    {
        // Generate kode proyek otomatis
        $year = date('Y');
        $month = date('m');
        $lastProject = Project::whereYear('created_at', $year)
                             ->whereMonth('created_at', $month)
                             ->orderBy('id', 'desc')
                             ->first();
        
        $sequence = $lastProject ? (int)substr($lastProject->code, -3) + 1 : 1;
        $code = 'PRJ-' . $year . '-' . $month . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);

        // Hitung total nilai plan
        $plannedServiceValue = (float)($row['nilai_jasa_plan'] ?? 0);
        $plannedMaterialValue = (float)($row['nilai_material_plan'] ?? 0);
        $plannedTotalValue = $plannedServiceValue + $plannedMaterialValue;

        Project::create([
            'code' => $code,
            'name' => $row['nama_proyek'],
            'description' => $row['deskripsi'] ?? '',
            'type' => $row['tipe'],
            'status' => $row['status'] ?? 'draft',
            'priority' => $row['prioritas'] ?? 'medium',
            'location' => $row['lokasi'] ?? '',
            'planned_service_value' => $plannedServiceValue,
            'planned_material_value' => $plannedMaterialValue,
            'planned_total_value' => $plannedTotalValue,
            'start_date' => $row['tanggal_mulai'] ? Carbon::parse($row['tanggal_mulai']) : null,
            'end_date' => $row['tanggal_selesai'] ? Carbon::parse($row['tanggal_selesai']) : null,
            'notes' => $row['catatan'] ?? '',
            'user_id' => Auth::id(),
        ]);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getErrorCount()
    {
        return $this->errorCount;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }
}
