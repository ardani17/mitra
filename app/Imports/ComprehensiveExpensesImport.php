<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\ProjectExpense;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class ComprehensiveExpensesImport implements ToCollection, WithHeadingRow
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
                    $this->createExpense($row);
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
            'deskripsi_pengeluaran' => 'required|string|max:255',
            'kategori' => 'nullable|in:material,tenaga_kerja,transportasi,konsumsi,lainnya',
            'jumlah_rp' => 'required|numeric|min:0',
            'tanggal_pengeluaran' => 'required|date',
            'nomor_kwitansi' => 'nullable|string|max:100',
            'status' => 'nullable|in:draft,submitted,approved,rejected',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->errors[] = "Row {$rowNumber}: {$error}";
            }
            $this->errorCount++;
        }
    }

    protected function createExpense($row)
    {
        // Find project by code
        $project = Project::where('code', $row['kode_proyek'])->first();
        
        if (!$project) {
            throw new \Exception("Project dengan kode {$row['kode_proyek']} tidak ditemukan");
        }

        ProjectExpense::create([
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'description' => $row['deskripsi_pengeluaran'],
            'category' => $row['kategori'] ?? 'lainnya',
            'amount' => (float)$row['jumlah_rp'],
            'expense_date' => Carbon::parse($row['tanggal_pengeluaran']),
            'receipt_number' => $row['nomor_kwitansi'] ?? null,
            'status' => $row['status'] ?? 'draft',
            'notes' => $row['catatan_tambahan'] ?? '',
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
