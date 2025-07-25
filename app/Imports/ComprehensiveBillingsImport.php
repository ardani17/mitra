<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\ProjectBilling;
use App\Models\BillingBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class ComprehensiveBillingsImport implements ToCollection, WithHeadingRow
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
                    $this->createBilling($row);
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
            'jumlah_tagihan_rp' => 'required|numeric|min:0',
            'tanggal_tagihan' => 'required|date',
            'tanggal_jatuh_tempo' => 'nullable|date|after_or_equal:tanggal_tagihan',
            'status_pembayaran' => 'nullable|in:draft,sent,paid,overdue,cancelled',
            'tanggal_pembayaran' => 'nullable|date',
            'persentase_tagihan' => 'nullable|numeric|min:0|max:1',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->errors[] = "Row {$rowNumber}: {$error}";
            }
            $this->errorCount++;
        }
    }

    protected function createBilling($row)
    {
        // Find project by code
        $project = Project::where('code', $row['kode_proyek'])->first();
        
        if (!$project) {
            throw new \Exception("Project dengan kode {$row['kode_proyek']} tidak ditemukan");
        }

        // Find or create billing batch if specified
        $billingBatchId = null;
        if (!empty($row['batch_billing'])) {
            $billingBatch = BillingBatch::firstOrCreate([
                'name' => $row['batch_billing'],
                'client_type' => $project->client_type ?? 'swasta'
            ], [
                'description' => 'Batch billing untuk ' . $row['batch_billing'],
                'status' => 'active'
            ]);
            $billingBatchId = $billingBatch->id;
        }

        ProjectBilling::create([
            'project_id' => $project->id,
            'billing_batch_id' => $billingBatchId,
            'sp_number' => $row['nomor_sp'] ?? null,
            'invoice_number' => $row['nomor_invoice'] ?? null,
            'tax_invoice_number' => $row['nomor_faktur_pajak'] ?? null,
            'amount' => (float)$row['jumlah_tagihan_rp'],
            'billing_date' => Carbon::parse($row['tanggal_tagihan']),
            'due_date' => $row['tanggal_jatuh_tempo'] ? Carbon::parse($row['tanggal_jatuh_tempo']) : null,
            'status' => $row['status_pembayaran'] ?? 'draft',
            'paid_date' => $row['tanggal_pembayaran'] ? Carbon::parse($row['tanggal_pembayaran']) : null,
            'percentage' => $row['persentase_tagihan'] ? (float)$row['persentase_tagihan'] : null,
            'notes' => $row['catatan'] ?? '',
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
