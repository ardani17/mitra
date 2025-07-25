<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class ComprehensiveProjectsImport implements ToCollection, WithHeadingRow
{
    protected $errors = [];
    protected $successCount = 0;
    protected $errorCount = 0;
    protected $previewMode = false;
    protected $confirmMode = false;
    protected $importValidOnly = true;
    protected $validData = [];
    protected $invalidData = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                // Skip empty rows
                if ($row->filter()->isEmpty()) {
                    continue;
                }

                // Skip instruction rows if they exist
                if (isset($row['nama_proyek']) && str_contains(strtolower($row['nama_proyek']), 'petunjuk')) {
                    continue;
                }

                $rowNumber = $index + 2; // +2 karena header di row 1 dan index dimulai dari 0
                
                // Process row based on mode
                if ($this->previewMode) {
                    $this->processRowForPreview($row, $rowNumber);
                } else {
                    // Normal import mode or confirm mode
                    if ($this->confirmMode) {
                        // In confirm mode, only import valid data
                        if ($this->isRowValid($row, $rowNumber)) {
                            $this->createProject($row);
                            $this->successCount++;
                        } else {
                            $this->errorCount++;
                        }
                    } else {
                        // Legacy import mode
                        if ($this->validateRow($row, $rowNumber)) {
                            $this->createProject($row);
                            $this->successCount++;
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                $this->errorCount++;
            }
        }
    }

    protected function validateRow($row, $rowNumber)
    {
        // Map column names to handle different variations
        $mappedRow = [
            'nama_proyek' => $row['nama_proyek'] ?? null,
            'tipe_proyek' => $row['tipe'] ?? $row['tipe_proyek'] ?? null,
            'status' => $row['status'] ?? null,
            'prioritas' => $row['prioritas'] ?? null,
            'tipe_klien' => $row['tipe_klien'] ?? null,
            'nilai_jasa_plan_rp' => $row['nilai_jasa_plan'] ?? $row['nilai_jasa_plan_rp'] ?? null,
            'nilai_material_plan_rp' => $row['nilai_material_plan'] ?? $row['nilai_material_plan_rp'] ?? null,
            'nilai_jasa_akhir_rp' => $row['nilai_jasa_akhir'] ?? $row['nilai_jasa_akhir_rp'] ?? null,
            'nilai_material_akhir_rp' => $row['nilai_material_akhir'] ?? $row['nilai_material_akhir_rp'] ?? null,
            'tanggal_mulai' => $row['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $row['tanggal_selesai'] ?? $row['tanggal_selesai'] ?? null,
            'progress' => $row['progress'] ?? null,
        ];

        $validator = Validator::make($mappedRow, [
            'nama_proyek' => 'required|string|max:255',
            'tipe_proyek' => 'required|in:konstruksi,maintenance,other',
            'status' => 'required|in:planning,in_progress,completed,cancelled',
            'prioritas' => 'nullable|in:low,medium,high,urgent',
            'tipe_klien' => 'nullable|in:pemerintah,swasta',
            'nilai_jasa_plan_rp' => 'nullable|numeric|min:0',
            'nilai_material_plan_rp' => 'nullable|numeric|min:0',
            'nilai_jasa_akhir_rp' => 'nullable|numeric|min:0',
            'nilai_material_akhir_rp' => 'nullable|numeric|min:0',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->errors[] = "Row {$rowNumber}: {$error}";
            }
            $this->errorCount++;
            return false;
        }

        return true;
    }

    protected function createProject($row)
    {
        // Generate kode proyek otomatis jika tidak ada
        $code = $row['kode_proyek'] ?? $this->generateProjectCode();

        // Map column names to handle different variations
        $tipe = $row['tipe'] ?? $row['tipe_proyek'] ?? 'other';
        $nilaiJasaPlan = (float)($row['nilai_jasa_plan'] ?? $row['nilai_jasa_plan_rp'] ?? 0);
        $nilaiMaterialPlan = (float)($row['nilai_material_plan'] ?? $row['nilai_material_plan_rp'] ?? 0);
        $nilaiJasaAkhir = (float)($row['nilai_jasa_akhir'] ?? $row['nilai_jasa_akhir_rp'] ?? 0);
        $nilaiMaterialAkhir = (float)($row['nilai_material_akhir'] ?? $row['nilai_material_akhir_rp'] ?? 0);

        // Hitung nilai plan
        $plannedTotalValue = $nilaiJasaPlan + $nilaiMaterialPlan;

        // Hitung nilai akhir
        $finalTotalValue = $nilaiJasaAkhir + $nilaiMaterialAkhir;

        Project::create([
            'code' => $code,
            'name' => $row['nama_proyek'],
            'description' => $row['deskripsi'] ?? '',
            'type' => $tipe,
            'status' => $row['status'] ?? 'planning',
            'priority' => $row['prioritas'] ?? 'medium',
            'location' => $row['lokasi'] ?? '',
            'client_type' => $row['tipe_klien'] ?? null,
            'planned_service_value' => $nilaiJasaPlan,
            'planned_material_value' => $nilaiMaterialPlan,
            'planned_total_value' => $plannedTotalValue,
            'final_service_value' => $nilaiJasaAkhir,
            'final_material_value' => $nilaiMaterialAkhir,
            'final_total_value' => $finalTotalValue,
            'start_date' => $row['tanggal_mulai'] ? Carbon::parse($row['tanggal_mulai']) : null,
            'end_date' => ($row['tanggal_selesai'] ?? $row['tanggal_selesai']) ? Carbon::parse($row['tanggal_selesai'] ?? $row['tanggal_selesai']) : null,
            'progress_percentage' => (int)($row['progress'] ?? 0),
            'notes' => $row['catatan'] ?? '',
            'user_id' => Auth::id(),
        ]);
    }

    protected function generateProjectCode()
    {
        $year = date('Y');
        $month = date('m');
        $lastProject = Project::whereYear('created_at', $year)
                             ->whereMonth('created_at', $month)
                             ->orderBy('id', 'desc')
                             ->first();
        
        $sequence = $lastProject ? (int)substr($lastProject->code, -3) + 1 : 1;
        return 'PRJ-' . $year . '-' . $month . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
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

    // Preview mode methods
    public function setPreviewMode($preview = true)
    {
        $this->previewMode = $preview;
    }

    public function setConfirmMode($confirm = true)
    {
        $this->confirmMode = $confirm;
    }

    public function setImportValidOnly($validOnly = true)
    {
        $this->importValidOnly = $validOnly;
    }

    public function getValidData()
    {
        return $this->validData;
    }

    public function getInvalidData()
    {
        return $this->invalidData;
    }

    protected function processRowForPreview($row, $rowNumber)
    {
        $mappedRow = $this->mapRowData($row);
        $validator = $this->createValidator($mappedRow);

        if ($validator->fails()) {
            // Invalid data
            $this->invalidData[] = [
                'row_number' => $rowNumber,
                'data' => $mappedRow,
                'errors' => $validator->errors()->all(),
                'original_row' => $row->toArray()
            ];
            $this->errorCount++;
        } else {
            // Valid data
            $this->validData[] = [
                'row_number' => $rowNumber,
                'data' => $mappedRow,
                'original_row' => $row->toArray()
            ];
            $this->successCount++;
        }
    }

    protected function isRowValid($row, $rowNumber)
    {
        $mappedRow = $this->mapRowData($row);
        $validator = $this->createValidator($mappedRow);
        return !$validator->fails();
    }

    protected function mapRowData($row)
    {
        return [
            'nama_proyek' => $row['nama_proyek'] ?? null,
            'deskripsi' => $row['deskripsi'] ?? '',
            'tipe_proyek' => $row['tipe'] ?? $row['tipe_proyek'] ?? null,
            'status' => $row['status'] ?? null,
            'prioritas' => $row['prioritas'] ?? null,
            'lokasi' => $row['lokasi'] ?? '',
            'tipe_klien' => $row['tipe_klien'] ?? null,
            'nilai_jasa_plan' => $row['nilai_jasa_plan'] ?? $row['nilai_jasa_plan_rp'] ?? 0,
            'nilai_material_plan' => $row['nilai_material_plan'] ?? $row['nilai_material_plan_rp'] ?? 0,
            'nilai_jasa_akhir' => $row['nilai_jasa_akhir'] ?? $row['nilai_jasa_akhir_rp'] ?? 0,
            'nilai_material_akhir' => $row['nilai_material_akhir'] ?? $row['nilai_material_akhir_rp'] ?? 0,
            'tanggal_mulai' => $row['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $row['tanggal_selesai'] ?? null,
            'progress' => $row['progress'] ?? 0,
            'catatan' => $row['catatan'] ?? '',
        ];
    }

    protected function createValidator($mappedRow)
    {
        return Validator::make($mappedRow, [
            'nama_proyek' => 'required|string|max:255',
            'tipe_proyek' => 'required|in:konstruksi,maintenance,other',
            'status' => 'required|in:planning,in_progress,completed,cancelled',
            'prioritas' => 'nullable|in:low,medium,high,urgent',
            'tipe_klien' => 'nullable|in:pemerintah,swasta',
            'nilai_jasa_plan' => 'nullable|numeric|min:0',
            'nilai_material_plan' => 'nullable|numeric|min:0',
            'nilai_jasa_akhir' => 'nullable|numeric|min:0',
            'nilai_material_akhir' => 'nullable|numeric|min:0',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);
    }
}
