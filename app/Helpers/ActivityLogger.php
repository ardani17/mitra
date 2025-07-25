<?php

namespace App\Helpers;

use App\Models\ProjectActivity;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Log aktivitas proyek
     */
    public static function log($projectId, $activityType, $description, $changes = null)
    {
        return ProjectActivity::create([
            'project_id' => $projectId,
            'user_id' => Auth::id(),
            'activity_type' => $activityType,
            'description' => $description,
            'changes' => $changes
        ]);
    }

    /**
     * Log pembuatan proyek
     */
    public static function logProjectCreated($project)
    {
        return self::log(
            $project->id,
            'project_created',
            "Proyek '{$project->name}' telah dibuat",
            [
                'project_name' => $project->name,
                'project_code' => $project->code,
                'budget' => $project->planned_budget,
                'status' => $project->status
            ]
        );
    }

    /**
     * Log update proyek
     */
    public static function logProjectUpdated($project, $originalData = [])
    {
        $changes = [];
        $description = "Proyek '{$project->name}' telah diperbarui";

        // Track specific changes
        if (isset($originalData['name']) && $originalData['name'] !== $project->name) {
            $changes['name'] = ['from' => $originalData['name'], 'to' => $project->name];
        }
        if (isset($originalData['status']) && $originalData['status'] !== $project->status) {
            $changes['status'] = ['from' => $originalData['status'], 'to' => $project->status];
            $description = "Status proyek '{$project->name}' diubah dari '{$originalData['status']}' ke '{$project->status}'";
        }
        if (isset($originalData['planned_budget']) && $originalData['planned_budget'] !== $project->planned_budget) {
            $changes['budget'] = ['from' => $originalData['planned_budget'], 'to' => $project->planned_budget];
        }

        return self::log($project->id, 'project_updated', $description, $changes);
    }

    /**
     * Log pengeluaran dibuat
     */
    public static function logExpenseCreated($expense)
    {
        return self::log(
            $expense->project_id,
            'expense_created',
            "Pengeluaran baru sebesar " . \App\Helpers\FormatHelper::formatRupiah($expense->amount) . " untuk '{$expense->description}'",
            [
                'expense_id' => $expense->id,
                'amount' => $expense->amount,
                'description' => $expense->description,
                'category' => $expense->category,
                'status' => $expense->status
            ]
        );
    }

    /**
     * Log approval pengeluaran
     */
    public static function logExpenseApproval($expense, $approval)
    {
        $statusText = $approval->status === 'approved' ? 'disetujui' : 'ditolak';
        $levelText = match($approval->level) {
            'finance_manager' => 'Finance Manager',
            'project_manager' => 'Project Manager',
            'direktur' => 'Direktur',
            default => ucfirst($approval->level)
        };

        return self::log(
            $expense->project_id,
            'expense_approval',
            "Pengeluaran '{$expense->description}' {$statusText} oleh {$levelText}",
            [
                'expense_id' => $expense->id,
                'approval_level' => $approval->level,
                'approval_status' => $approval->status,
                'notes' => $approval->notes,
                'amount' => $expense->amount
            ]
        );
    }

    /**
     * Log billing dibuat
     */
    public static function logBillingCreated($billing)
    {
        return self::log(
            $billing->project_id,
            'billing_created',
            "Invoice baru dibuat dengan nomor '{$billing->invoice_number}' sebesar " . \App\Helpers\FormatHelper::formatRupiah($billing->amount),
            [
                'billing_id' => $billing->id,
                'invoice_number' => $billing->invoice_number,
                'amount' => $billing->amount,
                'billing_date' => $billing->billing_date,
                'status' => $billing->status
            ]
        );
    }

    /**
     * Log billing batch dibuat
     */
    public static function logBillingBatchCreated($batch, $billingCount)
    {
        return self::log(
            null, // Batch bisa untuk multiple projects
            'billing_batch_created',
            "Batch billing '{$batch->batch_number}' dibuat dengan {$billingCount} invoice",
            [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'billing_count' => $billingCount,
                'total_amount' => $batch->total_amount,
                'client_type' => $batch->client_type
            ]
        );
    }

    /**
     * Log status billing berubah
     */
    public static function logBillingStatusChanged($billing, $oldStatus)
    {
        $statusText = match($billing->status) {
            'draft' => 'Draft',
            'sent' => 'Terkirim',
            'paid' => 'Terbayar',
            'overdue' => 'Terlambat',
            default => ucfirst($billing->status)
        };

        return self::log(
            $billing->project_id,
            'billing_status_changed',
            "Status invoice '{$billing->invoice_number}' diubah ke {$statusText}",
            [
                'billing_id' => $billing->id,
                'old_status' => $oldStatus,
                'new_status' => $billing->status,
                'amount' => $billing->amount
            ]
        );
    }

    /**
     * Log dokumen diunggah
     */
    public static function logDocumentUploaded($document)
    {
        return self::log(
            $document->project_id,
            'document_uploaded',
            "Dokumen '{$document->name}' diunggah ke kategori '{$document->category}'",
            [
                'document_id' => $document->id,
                'document_name' => $document->name,
                'category' => $document->category,
                'file_size' => $document->file_size,
                'file_type' => $document->file_type
            ]
        );
    }

    /**
     * Log timeline dibuat/diperbarui
     */
    public static function logTimelineUpdated($timeline, $isNew = false)
    {
        $action = $isNew ? 'dibuat' : 'diperbarui';
        
        return self::log(
            $timeline->project_id,
            $isNew ? 'timeline_created' : 'timeline_updated',
            "Timeline '{$timeline->phase_name}' {$action} dengan progress {$timeline->progress_percentage}%",
            [
                'timeline_id' => $timeline->id,
                'phase_name' => $timeline->phase_name,
                'progress_percentage' => $timeline->progress_percentage,
                'start_date' => $timeline->start_date,
                'end_date' => $timeline->end_date,
                'status' => $timeline->status
            ]
        );
    }

    /**
     * Log import data
     */
    public static function logDataImport($projectId, $importType, $successCount, $errorCount = 0)
    {
        $description = "Import {$importType}: {$successCount} berhasil";
        if ($errorCount > 0) {
            $description .= ", {$errorCount} gagal";
        }

        return self::log(
            $projectId,
            'data_import',
            $description,
            [
                'import_type' => $importType,
                'success_count' => $successCount,
                'error_count' => $errorCount
            ]
        );
    }

    /**
     * Log export data
     */
    public static function logDataExport($projectId, $exportType, $recordCount)
    {
        return self::log(
            $projectId,
            'data_export',
            "Export {$exportType}: {$recordCount} record diekspor",
            [
                'export_type' => $exportType,
                'record_count' => $recordCount
            ]
        );
    }

    /**
     * Log perubahan profit analysis
     */
    public static function logProfitAnalysisUpdated($analysis)
    {
        $netProfit = $analysis->total_revenue - $analysis->total_expenses;
        
        return self::log(
            $analysis->project_id,
            'profit_analysis_updated',
            "Analisis profit diperbarui - Laba bersih: " . \App\Helpers\FormatHelper::formatRupiah($netProfit),
            [
                'analysis_id' => $analysis->id,
                'total_revenue' => $analysis->total_revenue,
                'total_expenses' => $analysis->total_expenses,
                'net_profit' => $netProfit,
                'profit_margin' => $analysis->profit_margin
            ]
        );
    }

    /**
     * Log aktivitas custom
     */
    public static function logCustomActivity($projectId, $activityType, $description, $changes = null)
    {
        return self::log($projectId, $activityType, $description, $changes);
    }

    /**
     * Get aktivitas untuk proyek tertentu
     */
    public static function getProjectActivities($projectId, $limit = 50)
    {
        return ProjectActivity::where('project_id', $projectId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get aktivitas terbaru untuk dashboard
     */
    public static function getRecentActivities($limit = 20)
    {
        return ProjectActivity::with(['project', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
