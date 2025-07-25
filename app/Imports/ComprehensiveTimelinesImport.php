<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\ProjectTimeline;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class ComprehensiveTimelinesImport implements ToCollection, WithHeadingRow
{
    protected $errors = [];
    protected $successCount = 0;
    protected $errorCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                // Skip instruction rows if they exist
                if (isset($row['kode_proyek']) && str_contains(strtolower($row['kode_proyek']), 'petunjuk')) {
                    continue;
                }

                $this->validateRow($row, $index + 2);
                
                if (empty($this->errors)) {
                    $this->createTimeline($row);
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
            'kode_proyek' => 'required|string|exists:projects,code',
            'milestone' => 'required|string|max:255',
            'tanggal_rencana' => 'required|date',
            'tanggal_aktual' => 'nullable|date',
            'status' => 'nullable|in:planned,in_progress,completed,delayed,cancelled',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->errors[] = "Row {$rowNumber}: {$error}";
            }
            $this->errorCount++;
        }
    }

    protected function createTimeline($row)
    {
        // Find project by code
        $project = Project::where('code', $row['kode_proyek'])->first();
        
        if (!$project) {
            throw new \Exception("Project dengan kode {$row['kode_proyek']} tidak ditemukan");
        }

        // Determine status based on dates and progress
        $status = $row['status'] ?? 'planned';
        $progress = (int)($row['progress'] ?? 0);
        
        // Auto-determine status if not provided
        if (empty($row['status'])) {
            if ($row['tanggal_aktual']) {
                $status = 'completed';
                $progress = 100;
            } elseif ($progress > 0 && $progress < 100) {
                $status = 'in_progress';
            } elseif ($row['tanggal_rencana'] && Carbon::parse($row['tanggal_rencana'])->isPast() && $progress < 100) {
                $status = 'delayed';
            }
        }

        ProjectTimeline::create([
            'project_id' => $project->id,
            'milestone' => $row['milestone'],
            'description' => $row['deskripsi'] ?? '',
            'planned_date' => Carbon::parse($row['tanggal_rencana']),
            'actual_date' => $row['tanggal_aktual'] ? Carbon::parse($row['tanggal_aktual']) : null,
            'status' => $status,
            'progress_percentage' => $progress,
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
