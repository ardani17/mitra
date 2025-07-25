<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillingBatchRequest;
use App\Models\BillingBatch;
use App\Models\BillingDocument;
use App\Models\BillingStatusLog;
use App\Models\Project;
use App\Models\ProjectBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BillingBatchController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BillingBatch::with(['projectBillings.project'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('billing_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('billing_date', '<=', $request->date_to);
        }

        // Search by batch code or invoice number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('batch_code', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhere('tax_invoice_number', 'like', "%{$search}%");
            });
        }

        $batches = $query->paginate(15);

        return view('billing-batches.index', compact('batches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', BillingBatch::class);

        // Get projects that have final values and haven't been billed yet
        $availableProjects = Project::where(function($query) {
                // At least one of service or material value must be > 0
                $query->where('final_service_value', '>', 0)
                      ->orWhere('final_material_value', '>', 0);
            })
            ->whereRaw('COALESCE(final_service_value, 0) + COALESCE(final_material_value, 0) > 0')
            ->where(function($query) {
                // Projects that don't have any billing yet
                $query->whereDoesntHave('billings')
                    // OR projects that have billings but not in any batch
                    ->orWhereHas('billings', function($subQuery) {
                        $subQuery->whereNull('billing_batch_id');
                    });
            })
            ->orderBy('code')
            ->get();

        return view('billing-batches.create', compact('availableProjects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BillingBatchRequest $request)
    {
        DB::transaction(function () use ($request) {
            // Create billing batch
            $batch = BillingBatch::create([
                'batch_code' => BillingBatch::generateBatchCode(),
                'billing_date' => $request->billing_date,
                'pph_rate' => $request->pph_rate,
                'ppn_rate' => $request->ppn_rate,
                'client_type' => $request->client_type,
                'sp_number' => $request->sp_number,
                'invoice_number' => $request->invoice_number,
                'notes' => $request->notes,
                'status' => BillingBatch::STATUS_DRAFT
            ]);

            // Get selected projects
            $selectedProjects = Project::whereIn('id', $request->projects)
                ->get();
            
            // Get batch client type
            $batchClientType = $request->client_type;
            
            // Calculate totals with batch client type
            $totalDpp = 0;
            $totalPpn = 0;
            $totalPph = 0;
            $totalBilling = 0;
            
            foreach ($selectedProjects as $project) {
                $serviceValue = $project->final_service_value ?? 0;
                $materialValue = $project->final_material_value ?? 0;
                $dpp = $serviceValue + $materialValue;
                $totalDpp += $dpp;
                
                // Calculate PPN based on batch client type
                if ($batchClientType === 'wapu') {
                    // WAPU: PPN tidak ditagihkan
                    $ppnAmount = 0;
                } else {
                    // Non-WAPU: PPN ditagihkan normal
                    $ppnAmount = $dpp * ($request->ppn_rate / 100);
                }
                
                $pphAmount = $dpp * ($request->pph_rate / 100);
                
                $totalPpn += $ppnAmount;
                $totalPph += $pphAmount;
                $totalBilling += $dpp + $ppnAmount;
            }
            
            // Update batch with calculated amounts
            $batch->update([
                'total_base_amount' => $totalDpp,
                'ppn_amount' => $totalPpn,
                'pph_amount' => $totalPph,
                'total_billing_amount' => $totalBilling,
                'total_received_amount' => $totalBilling - $totalPph
            ]);

            // Create individual billings for each selected project
            foreach ($selectedProjects as $project) {
                $serviceValue = $project->final_service_value ?? 0;
                $materialValue = $project->final_material_value ?? 0;
                $dpp = $serviceValue + $materialValue;
                
                // Calculate PPN based on batch client type
                if ($batchClientType === 'wapu') {
                    $ppnAmount = 0;
                } else {
                    $ppnAmount = $dpp * ($request->ppn_rate / 100);
                }
                
                $pphAmount = $dpp * ($request->pph_rate / 100);
                $billingAmount = $dpp + $ppnAmount;
                $receivedAmount = $billingAmount - $pphAmount;
                
                // Create individual billing
                ProjectBilling::create([
                    'project_id' => $project->id,
                    'billing_batch_id' => $batch->id,
                    'billing_date' => $request->billing_date,
                    'nilai_jasa' => $serviceValue,
                    'nilai_material' => $materialValue,
                    'subtotal' => $dpp,
                    'ppn_rate' => $request->ppn_rate,
                    'ppn_amount' => $ppnAmount,
                    'total_amount' => $billingAmount,
                    'base_amount' => $dpp,
                    'pph_amount' => $pphAmount,
                    'received_amount' => $receivedAmount,
                    'status' => 'draft',
                    'notes' => 'Auto-generated from batch billing - Client Type: ' . ucfirst(str_replace('_', '-', $batchClientType))
                ]);
                
                // Update project client_type to match batch
                if ($project->client_type !== $batchClientType) {
                    $project->update(['client_type' => $batchClientType]);
                }
            }

            // Log initial status
            $batch->statusLogs()->create([
                'status' => BillingBatch::STATUS_DRAFT,
                'notes' => 'Batch billing dibuat',
                'user_id' => auth()->id()
            ]);
        });

        return redirect()->route('billing-batches.index')
            ->with('success', 'Batch billing berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BillingBatch $billingBatch)
    {
        $billingBatch->load([
            'projectBillings.project',
            'statusLogs.user',
            'documents.uploader'
        ]);

        return view('billing-batches.show', compact('billingBatch'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BillingBatch $billingBatch)
    {
        // Only allow editing if status is draft
        if ($billingBatch->status !== BillingBatch::STATUS_DRAFT) {
            return redirect()->route('billing-batches.show', $billingBatch)
                ->with('error', 'Batch billing hanya dapat diedit dalam status draft.');
        }

        $billingBatch->load('projectBillings.project');

        // Get available projects for adding to batch
        $availableProjects = Project::whereHas('billings', function($query) use ($billingBatch) {
            $query->where(function($q) use ($billingBatch) {
                $q->whereNull('billing_batch_id')
                  ->orWhere('billing_batch_id', $billingBatch->id);
            });
        })->with(['billings' => function($query) use ($billingBatch) {
            $query->where(function($q) use ($billingBatch) {
                $q->whereNull('billing_batch_id')
                  ->orWhere('billing_batch_id', $billingBatch->id);
            });
        }])->get();

        // Remove projects that are already in the batch from available list
        $currentProjectIds = $billingBatch->projectBillings->pluck('project_id')->toArray();
        $availableProjects = $availableProjects->filter(function($project) use ($currentProjectIds) {
            return !in_array($project->id, $currentProjectIds);
        });

        return view('billing-batches.edit', compact('billingBatch', 'availableProjects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BillingBatchRequest $request, BillingBatch $billingBatch)
    {
        // Only allow updating if status is draft
        if ($billingBatch->status !== BillingBatch::STATUS_DRAFT) {
            return redirect()->route('billing-batches.show', $billingBatch)
                ->with('error', 'Batch billing hanya dapat diupdate dalam status draft.');
        }

        DB::transaction(function () use ($request, $billingBatch) {
            // Remove all current project billings from batch
            $billingBatch->projectBillings()->update([
                'billing_batch_id' => null,
                'base_amount' => 0,
                'pph_amount' => 0,
                'received_amount' => 0
            ]);

            // Get new selected project billings with their projects
            $projectBillings = ProjectBilling::with('project')
                ->whereIn('id', $request->project_billings)
                ->get();
            
            // Calculate totals with WAPU/Non-WAPU logic
            $totalDpp = 0;
            $totalPpn = 0;
            $totalPph = 0;
            $totalBilling = 0;
            
            foreach ($projectBillings as $billing) {
                $dpp = $billing->nilai_jasa + $billing->nilai_material;
                $totalDpp += $dpp;
                
                // Calculate PPN based on client type
                if ($billing->project->client_type === 'wapu') {
                    // WAPU: PPN tidak ditagihkan
                    $ppnAmount = 0;
                } else {
                    // Non-WAPU: PPN ditagihkan normal
                    $ppnAmount = $dpp * ($request->ppn_rate / 100);
                }
                
                $pphAmount = $dpp * ($request->pph_rate / 100);
                
                $totalPpn += $ppnAmount;
                $totalPph += $pphAmount;
                $totalBilling += $dpp + $ppnAmount;
            }

            // Update batch with new data
            $billingBatch->update([
                'billing_date' => $request->billing_date,
                'pph_rate' => $request->pph_rate,
                'ppn_rate' => $request->ppn_rate,
                'sp_number' => $request->sp_number,
                'invoice_number' => $request->invoice_number,
                'notes' => $request->notes,
                'total_base_amount' => $totalDpp,
                'ppn_amount' => $totalPpn,
                'pph_amount' => $totalPph,
                'total_billing_amount' => $totalBilling,
                'total_received_amount' => $totalBilling - $totalPph
            ]);

            // Assign new project billings to batch and calculate individual amounts
            foreach ($projectBillings as $billing) {
                $dpp = $billing->nilai_jasa + $billing->nilai_material;
                
                // Calculate PPN based on client type
                if ($billing->project->client_type === 'wapu') {
                    $ppnAmount = 0;
                } else {
                    $ppnAmount = $dpp * ($request->ppn_rate / 100);
                }
                
                $pphAmount = $dpp * ($request->pph_rate / 100);
                $billingAmount = $dpp + $ppnAmount;
                $receivedAmount = $billingAmount - $pphAmount;
                
                $billing->update([
                    'billing_batch_id' => $billingBatch->id,
                    'base_amount' => $dpp,
                    'pph_amount' => $pphAmount,
                    'received_amount' => $receivedAmount
                ]);
            }
        });

        return redirect()->route('billing-batches.show', $billingBatch)
            ->with('success', 'Batch billing berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BillingBatch $billingBatch)
    {
        // Only allow deletion if status is draft
        if ($billingBatch->status !== BillingBatch::STATUS_DRAFT) {
            return redirect()->route('billing-batches.index')
                ->with('error', 'Batch billing hanya dapat dihapus dalam status draft.');
        }

        DB::transaction(function () use ($billingBatch) {
            // Remove batch reference from project billings
            $billingBatch->projectBillings()->update([
                'billing_batch_id' => null,
                'base_amount' => 0,
                'pph_amount' => 0,
                'received_amount' => 0
            ]);

            // Delete the batch (documents and logs will be deleted by cascade)
            $billingBatch->delete();
        });

        return redirect()->route('billing-batches.index')
            ->with('success', 'Batch billing berhasil dihapus.');
    }

    /**
     * Update batch status
     */
    public function updateStatus(Request $request, BillingBatch $billingBatch)
    {
        $validationRules = [
            'status' => 'required|in:sent,area_verification,area_revision,regional_verification,regional_revision,payment_entry_ho,paid,cancelled',
            'notes' => 'nullable|string|max:1000'
        ];

        // If status is payment_entry_ho, require invoice number (nomor faktur pajak)
        if ($request->status === BillingBatch::STATUS_PAYMENT_ENTRY_HO) {
            $validationRules['invoice_number'] = 'required|string|max:255';
        }

        $request->validate($validationRules);

        // Update invoice number if provided
        if ($request->filled('invoice_number')) {
            $billingBatch->update(['invoice_number' => $request->invoice_number]);
        }

        $billingBatch->updateStatus($request->status, $request->notes);

        // If status is paid, update all project billings status
        if ($request->status === BillingBatch::STATUS_PAID) {
            $billingBatch->projectBillings()->update([
                'status' => 'paid',
                'paid_date' => now()
            ]);
        }

        return redirect()->route('billing-batches.show', $billingBatch)
            ->with('success', 'Status batch billing berhasil diupdate.');
    }

    /**
     * Upload document
     */
    public function uploadDocument(Request $request, BillingBatch $billingBatch)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'stage' => 'required|in:initial,area_revision,regional_revision,supporting_document',
            'document_type' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500'
        ]);

        $file = $request->file('document');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('billing-documents', $fileName, 'public');

        $billingBatch->documents()->create([
            'stage' => $request->stage,
            'document_type' => $request->document_type,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $request->description,
            'uploaded_by' => auth()->id()
        ]);

        return redirect()->route('billing-batches.show', $billingBatch)
            ->with('success', 'Dokumen berhasil diupload.');
    }

    /**
     * Delete document
     */
    public function deleteDocument(BillingBatch $billingBatch, $documentId)
    {
        $document = $billingBatch->documents()->findOrFail($documentId);
        
        // Delete file from storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();

        return redirect()->route('billing-batches.show', $billingBatch)
            ->with('success', 'Dokumen berhasil dihapus.');
    }
}
