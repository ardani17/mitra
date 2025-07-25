<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillingRequest;
use App\Models\Project;
use App\Models\ProjectBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProjectBilling::with('project')
            ->orderBy('created_at', 'desc');

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

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

        // Search by invoice number or SP number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('sp_number', 'like', "%{$search}%")
                  ->orWhere('tax_invoice_number', 'like', "%{$search}%");
            });
        }

        $billings = $query->paginate(15);
        $projects = Project::orderBy('name')->get();

        return view('billings.index', compact('billings', 'projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $projects = Project::orderBy('name')->get();
        $selectedProject = null;
        
        if ($request->filled('project_id')) {
            $selectedProject = Project::find($request->project_id);
        }

        return view('billings.create', compact('projects', 'selectedProject'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BillingRequest $request)
    {
        DB::transaction(function () use ($request) {
            $project = Project::findOrFail($request->project_id);
            
            // Calculate PPN based on client type
            $dpp = $request->nilai_jasa + $request->nilai_material;
            
            if ($project->client_type === 'wapu') {
                // WAPU: PPN tidak ditagihkan
                $ppnAmount = 0;
            } else {
                // Non-WAPU: PPN ditagihkan normal
                $ppnAmount = $dpp * ($request->ppn_rate / 100);
            }
            
            $totalAmount = $dpp + $ppnAmount;

            ProjectBilling::create([
                'project_id' => $request->project_id,
                'billing_date' => $request->billing_date,
                'invoice_number' => $request->invoice_number,
                'sp_number' => $request->sp_number,
                'tax_invoice_number' => $request->tax_invoice_number,
                'nilai_jasa' => $request->nilai_jasa,
                'nilai_material' => $request->nilai_material,
                'ppn_rate' => $request->ppn_rate,
                'ppn_amount' => $ppnAmount,
                'total_amount' => $totalAmount,
                'description' => $request->description,
                'status' => 'draft'
            ]);
        });

        return redirect()->route('billings.index')
            ->with('success', 'Penagihan berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectBilling $billing)
    {
        $billing->load('project');
        return view('billings.show', compact('billing'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectBilling $billing)
    {
        // Only allow editing if status is draft
        if ($billing->status !== 'draft') {
            return redirect()->route('billings.show', $billing)
                ->with('error', 'Penagihan hanya dapat diedit dalam status draft.');
        }

        $projects = Project::orderBy('name')->get();
        return view('billings.edit', compact('billing', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BillingRequest $request, ProjectBilling $billing)
    {
        // Only allow updating if status is draft
        if ($billing->status !== 'draft') {
            return redirect()->route('billings.show', $billing)
                ->with('error', 'Penagihan hanya dapat diupdate dalam status draft.');
        }

        DB::transaction(function () use ($request, $billing) {
            $project = Project::findOrFail($request->project_id);
            
            // Calculate PPN based on client type
            $dpp = $request->nilai_jasa + $request->nilai_material;
            
            if ($project->client_type === 'wapu') {
                // WAPU: PPN tidak ditagihkan
                $ppnAmount = 0;
            } else {
                // Non-WAPU: PPN ditagihkan normal
                $ppnAmount = $dpp * ($request->ppn_rate / 100);
            }
            
            $totalAmount = $dpp + $ppnAmount;

            $billing->update([
                'project_id' => $request->project_id,
                'billing_date' => $request->billing_date,
                'invoice_number' => $request->invoice_number,
                'sp_number' => $request->sp_number,
                'tax_invoice_number' => $request->tax_invoice_number,
                'nilai_jasa' => $request->nilai_jasa,
                'nilai_material' => $request->nilai_material,
                'ppn_rate' => $request->ppn_rate,
                'ppn_amount' => $ppnAmount,
                'total_amount' => $totalAmount,
                'description' => $request->description
            ]);
        });

        return redirect()->route('billings.show', $billing)
            ->with('success', 'Penagihan berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectBilling $billing)
    {
        // Only allow deletion if status is draft
        if ($billing->status !== 'draft') {
            return redirect()->route('billings.index')
                ->with('error', 'Penagihan hanya dapat dihapus dalam status draft.');
        }

        $billing->delete();

        return redirect()->route('billings.index')
            ->with('success', 'Penagihan berhasil dihapus.');
    }

    /**
     * Update billing status
     */
    public function updateStatus(Request $request, ProjectBilling $billing)
    {
        $request->validate([
            'status' => 'required|in:sent,paid,cancelled',
            'notes' => 'nullable|string|max:1000'
        ]);

        $billing->update([
            'status' => $request->status,
            'paid_date' => $request->status === 'paid' ? now() : null
        ]);

        return redirect()->route('billings.show', $billing)
            ->with('success', 'Status penagihan berhasil diupdate.');
    }
}
