<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'client_name',
        'client_type',
        'client',
        'planned_budget',
        'planned_service_value',
        'planned_material_value',
        'planned_total_value',
        'actual_budget',
        'final_service_value',
        'final_material_value',
        'final_total_value',
        'start_date',
        'end_date',
        'status',
        'billing_status',
        'latest_po_number',
        'latest_sp_number',
        'latest_invoice_number',
        'total_billed_amount',
        'billing_percentage',
        'last_billing_date',
        'priority',
        'location',
        'notes',
    ];

    protected $casts = [
        'planned_budget' => 'decimal:2',
        'planned_service_value' => 'decimal:2',
        'planned_material_value' => 'decimal:2',
        'planned_total_value' => 'decimal:2',
        'actual_budget' => 'decimal:2',
        'final_service_value' => 'decimal:2',
        'final_material_value' => 'decimal:2',
        'final_total_value' => 'decimal:2',
        'total_billed_amount' => 'decimal:2',
        'billing_percentage' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'last_billing_date' => 'date'
    ];


    public function expenses(): HasMany
    {
        return $this->hasMany(ProjectExpense::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class);
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(ProjectTimeline::class);
    }

    public function billings(): HasMany
    {
        return $this->hasMany(ProjectBilling::class);
    }

    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(ProjectPaymentSchedule::class);
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(ProjectRevenue::class);
    }

    public function profitAnalyses(): HasMany
    {
        return $this->hasMany(ProjectProfitAnalysis::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function cashflowEntries(): HasMany
    {
        return $this->hasMany(CashflowEntry::class);
    }

    /**
     * Check if project can be safely deleted
     */
    public function canBeDeleted(): array
    {
        $warnings = [];
        $blockers = [];

        // Check for active billings
        $activeBillings = $this->billings()->whereNotNull('invoice_number')->count();
        if ($activeBillings > 0) {
            $warnings[] = "Proyek memiliki {$activeBillings} tagihan aktif yang akan ikut terhapus";
        }

        // Check for approved expenses
        $approvedExpenses = $this->expenses()->where('status', 'approved')->count();
        if ($approvedExpenses > 0) {
            $warnings[] = "Proyek memiliki {$approvedExpenses} pengeluaran yang sudah disetujui";
        }

        // Check for pending expense approvals
        $pendingExpenses = $this->expenses()->where('status', 'pending')->count();
        if ($pendingExpenses > 0) {
            $warnings[] = "Proyek memiliki {$pendingExpenses} pengeluaran yang menunggu persetujuan";
        }

        // Check for documents
        $documentsCount = $this->documents()->count();
        if ($documentsCount > 0) {
            $warnings[] = "Proyek memiliki {$documentsCount} dokumen yang akan ikut terhapus";
        }

        // Check if project is completed and billed
        if ($this->status === 'completed' && $this->billing_status === 'fully_billed') {
            $warnings[] = "Proyek sudah selesai dan sudah ditagih penuh - pertimbangkan untuk mengarsipkan daripada menghapus";
        }

        return [
            'can_delete' => empty($blockers),
            'warnings' => $warnings,
            'blockers' => $blockers
        ];
    }

    /**
     * Get deletion summary for confirmation
     */
    public function getDeletionSummary(): array
    {
        return [
            'project_name' => $this->name,
            'project_code' => $this->code,
            'expenses_count' => $this->expenses()->count(),
            'expense_approvals_count' => \App\Models\ExpenseApproval::whereIn('expense_id', $this->expenses()->pluck('id'))->count(),
            'activities_count' => $this->activities()->count(),
            'timelines_count' => $this->timelines()->count(),
            'billings_count' => $this->billings()->count(),
            'revenues_count' => $this->revenues()->count(),
            'revenue_items_count' => \App\Models\RevenueItem::whereIn('revenue_id', $this->revenues()->pluck('id'))->count(),
            'documents_count' => $this->documents()->count(),
            'profit_analyses_count' => $this->profitAnalyses()->count(),
            'total_billed_amount' => $this->total_billed_amount,
            'total_expenses_amount' => $this->total_expenses
        ];
    }

    /**
     * Boot method untuk handle cascade delete
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($project) {
            try {
                \DB::beginTransaction();

                // Log deletion attempt
                \Log::info('Starting project deletion', [
                    'project_id' => $project->id,
                    'project_name' => $project->name,
                    'user_id' => auth()->id()
                ]);

                // Get deletion summary for logging
                $summary = $project->getDeletionSummary();

                // 1. Delete expense approvals first (through expenses)
                $expenseIds = $project->expenses()->pluck('id');
                if ($expenseIds->isNotEmpty()) {
                    $deletedApprovals = \App\Models\ExpenseApproval::whereIn('expense_id', $expenseIds)->delete();
                    \Log::info("Deleted {$deletedApprovals} expense approvals");
                }

                // 2. Delete revenue items before deleting revenues
                $totalRevenueItems = 0;
                foreach ($project->revenues as $revenue) {
                    $itemsCount = $revenue->revenueItems()->count();
                    $revenue->revenueItems()->delete();
                    $totalRevenueItems += $itemsCount;
                }
                if ($totalRevenueItems > 0) {
                    \Log::info("Deleted {$totalRevenueItems} revenue items");
                }

                // 3. Delete documents and their physical files
                $deletedFiles = 0;
                foreach ($project->documents as $document) {
                    // Delete physical file if exists
                    if ($document->file_path && \Storage::exists($document->file_path)) {
                        \Storage::delete($document->file_path);
                        $deletedFiles++;
                    }
                    $document->delete();
                }
                if ($deletedFiles > 0) {
                    \Log::info("Deleted {$deletedFiles} physical files");
                }

                // 4. Delete main related records
                $deletedExpenses = $project->expenses()->delete();
                $deletedActivities = $project->activities()->delete();
                $deletedTimelines = $project->timelines()->delete();
                $deletedBillings = $project->billings()->delete();
                $deletedRevenues = $project->revenues()->delete();
                $deletedProfitAnalyses = $project->profitAnalyses()->delete();

                \Log::info('Deleted related records', [
                    'expenses' => $deletedExpenses,
                    'activities' => $deletedActivities,
                    'timelines' => $deletedTimelines,
                    'billings' => $deletedBillings,
                    'revenues' => $deletedRevenues,
                    'profit_analyses' => $deletedProfitAnalyses
                ]);

                // 5. Log deletion activity for audit trail
                \App\Helpers\ActivityLogger::log(
                    $project->id, 
                    'deleted', 
                    'Project deleted with all related data: ' . json_encode($summary)
                );

                \DB::commit();

                \Log::info('Project deletion completed successfully', [
                    'project_id' => $project->id,
                    'summary' => $summary
                ]);

            } catch (\Exception $e) {
                \DB::rollback();
                \Log::error('Failed to delete project ' . $project->id . ': ' . $e->getMessage(), [
                    'project_id' => $project->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw new \Exception('Gagal menghapus proyek: ' . $e->getMessage());
            }
        });
    }

    public function getTotalExpensesAttribute()
    {
        return $this->expenses()->where('status', 'approved')->sum('amount');
    }
    
    /**
     * Get project progress percentage based on timeline milestones
     */
    public function getProgressPercentageAttribute()
    {
        $totalTimelines = $this->timelines->count();
        
        if ($totalTimelines === 0) {
            return 0;
        }
        
        return round($this->timelines->avg('progress_percentage'), 1);
    }
    
    /**
     * Get timeline status distribution
     */
    public function getTimelineStatusDistributionAttribute()
    {
        return [
            'planned' => $this->timelines->where('status', 'planned')->count(),
            'in_progress' => $this->timelines->where('status', 'in_progress')->count(),
            'completed' => $this->timelines->where('status', 'completed')->count(),
            'delayed' => $this->timelines->where('status', 'delayed')->count(),
        ];
    }

    public function getApprovedExpensesAttribute()
    {
        return $this->expenses()->where('status', 'approved')->get();
    }

    public function getPendingExpensesAttribute()
    {
        return $this->expenses()->where('status', 'pending')->get();
    }

    public function getRejectedExpensesAttribute()
    {
        return $this->expenses()->where('status', 'rejected')->get();
    }

    public function getTotalPendingExpensesAttribute()
    {
        return $this->expenses()->where('status', 'pending')->sum('amount');
    }

    public function getNetProfitAttribute()
    {
        $totalRevenue = $this->revenues->sum('total_amount');
        return $totalRevenue - $this->total_expenses;
    }

    public function getProfitMarginAttribute()
    {
        $totalRevenue = $this->revenues->sum('total_amount');
        if ($totalRevenue > 0) {
            return ($this->net_profit / $totalRevenue) * 100;
        }
        return 0;
    }

    /**
     * Get client type label
     */
    public function getClientTypeLabelAttribute()
    {
        return match($this->client_type) {
            'wapu' => 'WAPU (BUMN/Pemerintah)',
            'non_wapu' => 'Non-WAPU (Umum)',
            default => 'Non-WAPU (Umum)'
        };
    }

    /**
     * Get client type badge color
     */
    public function getClientTypeBadgeColorAttribute()
    {
        return match($this->client_type) {
            'wapu' => 'bg-blue-100 text-blue-800',
            'non_wapu' => 'bg-green-100 text-green-800',
            default => 'bg-green-100 text-green-800'
        };
    }

    /**
     * Check if project is WAPU
     */
    public function isWapu()
    {
        return $this->client_type === 'wapu';
    }

    /**
     * Check if project is Non-WAPU
     */
    public function isNonWapu()
    {
        return $this->client_type === 'non_wapu';
    }

    // ========== BILLING INTEGRATION METHODS ==========

    /**
     * Get current billing status dari billing batch dan project billing
     */
    public function getCurrentBillingStatusAttribute()
    {
        $latestBilling = $this->billings()->with('billingBatch')->latest()->first();
        
        if (!$latestBilling) {
            return 'not_billed';
        }
        
        // Jika ada billing batch, gunakan status dari batch
        if ($latestBilling->billingBatch) {
            return $latestBilling->billingBatch->status;
        }
        
        // Jika direct project billing, gunakan status dari project billing
        return $latestBilling->status ?? 'not_billed';
    }

    /**
     * Get total tagihan amount berdasarkan jenis billing yang digunakan
     */
    public function getTotalTagihanAmountAttribute()
    {
        // Prioritas: Jika ada billing batch, gunakan itu. Jika tidak, gunakan direct billing
        $hasBatchBilling = $this->billings()->whereHas('billingBatch')->exists();
        
        if ($hasBatchBilling) {
            // Gunakan data dari billing batch
            $batchBillings = $this->billings()->whereHas('billingBatch')->with('billingBatch')->get();
            $totalFromBatch = $batchBillings->sum(fn($billing) => $billing->billingBatch->total_received_amount ?? 0);
            
            if ($totalFromBatch > 0) {
                return $totalFromBatch;
            }
        } else {
            // Gunakan data dari direct project billing
            $directBillings = $this->billings()->whereNull('billing_batch_id')->get();
            $totalFromDirect = $directBillings->sum(fn($billing) =>
                $billing->status === 'paid'
                    ? ($billing->received_amount ?? $billing->total_amount ?? 0)
                    : ($billing->total_amount ?? 0)
            );
            
            if ($totalFromDirect > 0) {
                return $totalFromDirect;
            }
        }
        
        // Fallback jika tidak ada billing
        if ($this->final_total_value && $this->final_total_value > 0) {
            return $this->final_total_value;
        }
        
        return $this->planned_total_value ?? 0;
    }

    /**
     * Get label untuk total tagihan berdasarkan jenis billing yang digunakan
     */
    public function getTotalTagihanLabelAttribute()
    {
        $hasBatchBilling = $this->billings()->whereHas('billingBatch')->exists();
        
        if ($hasBatchBilling) {
            return 'Total Tagihan Batch';
        } elseif ($this->billings()->whereNull('billing_batch_id')->exists()) {
            return 'Total Tagihan Termin';
        }
        
        if ($this->final_total_value && $this->final_total_value > 0) {
            return 'Total Tagihan Akhir';
        }
        
        return 'Total Tagihan Plan';
    }

    /**
     * Get source type untuk total tagihan
     */
    public function getTotalTagihanSourceAttribute()
    {
        $hasBatchBilling = $this->billings()->whereHas('billingBatch')->exists();
        
        if ($hasBatchBilling) {
            return 'batch';
        } elseif ($this->billings()->whereNull('billing_batch_id')->exists()) {
            return 'direct';
        }
        
        if ($this->final_total_value && $this->final_total_value > 0) {
            return 'final';
        }
        
        return 'plan';
    }

    /**
     * Get total amount yang sudah diterima berdasarkan jenis billing yang digunakan
     */
    public function getTotalReceivedAmountAttribute()
    {
        // Prioritas: Jika ada billing batch, gunakan itu. Jika tidak, gunakan direct billing
        $hasBatchBilling = $this->billings()->whereHas('billingBatch')->exists();
        
        if ($hasBatchBilling) {
            // Gunakan data dari billing batch
            $batchBillings = $this->billings()->whereHas('billingBatch')->with('billingBatch')->get();
            return $batchBillings->sum(fn($billing) => $billing->billingBatch->total_received_amount ?? 0);
        } else {
            // Untuk project billing, hitung semua tagihan yang sudah dibuat
            $directBillings = $this->billings()->whereNull('billing_batch_id')->get();
            return $directBillings->sum(fn($billing) => $billing->total_amount ?? 0);
        }
    }

    /**
     * Get progress percentage berdasarkan jenis billing yang digunakan
     */
    public function getBillingProgressPercentageAttribute()
    {
        $hasBatchBilling = $this->billings()->whereHas('billingBatch')->exists();
        
        if ($hasBatchBilling) {
            // Untuk billing batch, gunakan progress berdasarkan status
            $currentStatus = $this->current_billing_status;
            return $this->getProgressFromStatus($currentStatus);
        } else {
            // Untuk project billing, hitung progress berdasarkan pembayaran termin
            return $this->calculateTerminPaymentProgress();
        }
    }

    /**
     * Calculate progress pembayaran termin untuk project billing
     */
    private function calculateTerminPaymentProgress(): int
    {
        // Tentukan total yang harus dibayar (prioritas: final_total_value > planned_total_value)
        $totalToBePaid = $this->final_total_value && $this->final_total_value > 0
            ? $this->final_total_value
            : ($this->planned_total_value ?? 0);
        
        if ($totalToBePaid <= 0) {
            return 0;
        }
        
        // Hitung total tagihan yang sudah dibuat
        $totalBilled = $this->billings()
            ->whereNull('billing_batch_id')
            ->sum('total_amount');
        
        // Hitung persentase berdasarkan total tagihan yang dibuat vs total yang harus dibayar
        $percentage = ($totalBilled / $totalToBePaid) * 100;
        
        return min(100, max(0, round($percentage)));
    }

    /**
     * Get latest billing info dari billing batch dan project billing
     */
    public function getLatestBillingInfoAttribute()
    {
        // Cari billing terakhir (baik dari batch maupun direct)
        $latestBilling = $this->billings()->with('billingBatch')->latest()->first();
        
        if (!$latestBilling) {
            return [
                'status' => 'not_billed',
                'status_label' => 'Belum Ditagih',
                'invoice_number' => null,
                'sp_number' => null,
                'billing_date' => null,
                'total_received' => 0,
                'source' => null
            ];
        }

        // Jika billing terkait dengan batch
        if ($latestBilling->billingBatch) {
            $batch = $latestBilling->billingBatch;
            return [
                'status' => $batch->status,
                'status_label' => $batch->status_label,
                'invoice_number' => $batch->invoice_number,
                'sp_number' => $batch->sp_number,
                'billing_date' => $batch->billing_date,
                'total_received' => $batch->total_received_amount,
                'source' => 'batch'
            ];
        }
        
        // Jika billing langsung (direct project billing)
        $statusLabels = [
            'draft' => 'Draft',
            'sent' => 'Terkirim',
            'paid' => 'Lunas',
            'overdue' => 'Terlambat',
            'cancelled' => 'Dibatalkan'
        ];
        
        return [
            'status' => $latestBilling->status,
            'status_label' => $statusLabels[$latestBilling->status] ?? 'Tidak Diketahui',
            'invoice_number' => $latestBilling->invoice_number,
            'sp_number' => $latestBilling->sp_number,
            'billing_date' => $latestBilling->billing_date,
            'total_received' => $latestBilling->received_amount ?? $latestBilling->total_amount ?? 0,
            'source' => 'direct'
        ];
    }

    /**
     * Get billing information berdasarkan jenis billing yang digunakan
     */
    public function getBillingInfoAttribute()
    {
        $hasBatchBilling = $this->billings()->whereHas('billingBatch')->exists();
        
        if ($hasBatchBilling) {
            // Gunakan billing batch
            $batchBillings = $this->billings()->whereHas('billingBatch')->with('billingBatch')->get();
            $batchInfo = [];
            $totalAmount = 0;
            
            foreach ($batchBillings as $billing) {
                $batch = $billing->billingBatch;
                $batchInfo[] = [
                    'id' => $billing->id,
                    'batch_code' => $batch->batch_code,
                    'invoice_number' => $batch->invoice_number,
                    'sp_number' => $batch->sp_number,
                    'billing_date' => $batch->billing_date,
                    'status' => $batch->status,
                    'status_label' => $batch->status_label,
                    'total_amount' => $batch->total_received_amount ?? 0,
                    'source' => 'batch'
                ];
                $totalAmount += $batch->total_received_amount ?? 0;
            }
            
            return [
                'type' => 'batch',
                'billings' => $batchInfo,
                'total_amount' => $totalAmount,
                'billing_count' => count($batchInfo)
            ];
        } else {
            // Gunakan direct billing
            $directBillings = $this->billings()->whereNull('billing_batch_id')->get();
            $directInfo = [];
            $totalAmount = 0;
            
            $statusLabels = [
                'draft' => 'Draft',
                'sent' => 'Terkirim',
                'paid' => 'Lunas',
                'overdue' => 'Terlambat',
                'cancelled' => 'Dibatalkan'
            ];
            
            foreach ($directBillings as $billing) {
                $directInfo[] = [
                    'id' => $billing->id,
                    'termin_label' => $billing->getTerminLabel(),
                    'invoice_number' => $billing->invoice_number,
                    'sp_number' => $billing->sp_number,
                    'billing_date' => $billing->billing_date,
                    'status' => $billing->status,
                    'status_label' => $statusLabels[$billing->status] ?? 'Tidak Diketahui',
                    'total_amount' => $billing->total_amount ?? 0,
                    'received_amount' => $billing->received_amount ?? 0,
                    'source' => 'direct'
                ];
                
                if ($billing->status === 'paid') {
                    $totalAmount += $billing->received_amount ?? $billing->total_amount ?? 0;
                } else {
                    $totalAmount += $billing->total_amount ?? 0;
                }
            }
            
            return [
                'type' => 'direct',
                'billings' => $directInfo,
                'total_amount' => $totalAmount,
                'billing_count' => count($directInfo)
            ];
        }
    }

    /**
     * Get progress dari status billing batch
     */
    private function getProgressFromStatus($status): int
    {
        return match($status) {
            'draft' => 0,
            'sent' => 15,
            'area_verification' => 30,
            'area_revision' => 25,
            'regional_verification' => 60,
            'regional_revision' => 55,
            'payment_entry_ho' => 80,
            'paid' => 100,
            'not_billed' => 0,
            default => 0
        };
    }

    /**
     * Get billing status label berdasarkan jenis billing yang digunakan
     */
    public function getBillingStatusLabelAttribute()
    {
        $hasBatchBilling = $this->billings()->whereHas('billingBatch')->exists();
        
        if ($hasBatchBilling) {
            // Untuk billing batch, gunakan status batch
            $currentStatus = $this->current_billing_status;
            
            return match($currentStatus) {
                'draft' => 'Draft',
                'sent' => 'Terkirim',
                'area_verification' => 'Verifikasi Area',
                'area_revision' => 'Revisi Area',
                'regional_verification' => 'Verifikasi Regional',
                'regional_revision' => 'Revisi Regional',
                'payment_entry_ho' => 'Entry Pembayaran HO',
                'paid' => 'Lunas',
                'cancelled' => 'Dibatalkan',
                'not_billed' => 'Belum Ditagih',
                default => 'Belum Ditagih'
            };
        } else {
            // Untuk project billing, gunakan status berdasarkan progress pembayaran
            $progress = $this->calculateTerminPaymentProgress();
            $hasDirectBilling = $this->billings()->whereNull('billing_batch_id')->exists();
            
            if (!$hasDirectBilling) {
                return 'Belum Ditagih';
            }
            
            if ($progress >= 100) {
                return 'Lunas';
            } elseif ($progress > 0) {
                return 'Sebagian Dibayar';
            } else {
                return 'Belum Dibayar';
            }
        }
    }

    /**
     * Get billing status badge color berdasarkan jenis billing yang digunakan
     */
    public function getBillingStatusBadgeColorAttribute()
    {
        $hasBatchBilling = $this->billings()->whereHas('billingBatch')->exists();
        
        if ($hasBatchBilling) {
            // Untuk billing batch, gunakan warna berdasarkan status batch
            $currentStatus = $this->current_billing_status;
            
            return match($currentStatus) {
                'draft' => 'bg-gray-100 text-gray-800',
                'sent' => 'bg-blue-100 text-blue-800',
                'area_verification' => 'bg-yellow-100 text-yellow-800',
                'area_revision' => 'bg-orange-100 text-orange-800',
                'regional_verification' => 'bg-purple-100 text-purple-800',
                'regional_revision' => 'bg-red-100 text-red-800',
                'payment_entry_ho' => 'bg-indigo-100 text-indigo-800',
                'paid' => 'bg-green-100 text-green-800',
                'cancelled' => 'bg-red-100 text-red-800',
                'not_billed' => 'bg-gray-100 text-gray-800',
                default => 'bg-gray-100 text-gray-800'
            };
        } else {
            // Untuk project billing, gunakan warna berdasarkan progress pembayaran
            $progress = $this->calculateTerminPaymentProgress();
            $hasDirectBilling = $this->billings()->whereNull('billing_batch_id')->exists();
            
            if (!$hasDirectBilling) {
                return 'bg-gray-100 text-gray-800';
            }
            
            if ($progress >= 100) {
                return 'bg-green-100 text-green-800';
            } elseif ($progress > 0) {
                return 'bg-yellow-100 text-yellow-800';
            } else {
                return 'bg-red-100 text-red-800';
            }
        }
    }

    /**
     * Update billing status berdasarkan total tagihan
     */
    public function updateBillingStatus(): void
    {
        $totalBilled = $this->billings()->sum('total_amount');
        $plannedValue = $this->planned_total_value ?? 0;
        
        $this->total_billed_amount = $totalBilled;
        
        if ($plannedValue > 0) {
            $this->billing_percentage = ($totalBilled / $plannedValue) * 100;
        } else {
            $this->billing_percentage = 0;
        }

        // Tentukan status berdasarkan persentase
        if ($totalBilled == 0) {
            $this->billing_status = 'not_billed';
        } elseif ($this->billing_percentage >= 100) {
            $this->billing_status = 'fully_billed';
        } else {
            $this->billing_status = 'partially_billed';
        }

        // Update dokumen tagihan terakhir
        $latestBilling = $this->billings()
            ->whereNotNull('invoice_number')
            ->orderBy('billing_date', 'desc')
            ->first();

        if ($latestBilling) {
            $this->latest_invoice_number = $latestBilling->invoice_number;
            $this->latest_sp_number = $latestBilling->sp_number;
            $this->last_billing_date = $latestBilling->billing_date;
            
            // Ambil PO number dari billing batch jika ada
            if ($latestBilling->billingBatch) {
                $this->latest_po_number = $latestBilling->billingBatch->sp_number;
            }
        }

        $this->save();
    }

    /**
     * Get latest billing documents
     */
    public function getLatestBillingDocumentsAttribute()
    {
        return [
            'po_number' => $this->latest_po_number,
            'sp_number' => $this->latest_sp_number,
            'invoice_number' => $this->latest_invoice_number,
            'billing_date' => $this->last_billing_date
        ];
    }

    /**
     * Check if project sudah ditagih penuh
     */
    public function isFullyBilled(): bool
    {
        return $this->billing_status === 'fully_billed';
    }

    /**
     * Check if project belum ditagih sama sekali
     */
    public function isNotBilled(): bool
    {
        return $this->billing_status === 'not_billed';
    }

    /**
     * Check if project sebagian ditagih
     */
    public function isPartiallyBilled(): bool
    {
        return $this->billing_status === 'partially_billed';
    }

    /**
     * Get remaining amount yang belum ditagih
     */
    public function getRemainingBillableAmountAttribute()
    {
        $plannedValue = $this->planned_total_value ?? 0;
        return $plannedValue - $this->total_billed_amount;
    }

    /**
     * Check if project has termin payment schedule
     */
    public function hasTerminSchedule(): bool
    {
        return $this->paymentSchedules()->count() > 0;
    }

    /**
     * Get termin payment summary
     */
    public function getTerminSummaryAttribute()
    {
        $schedules = $this->paymentSchedules;
        
        return [
            'total_schedules' => $schedules->count(),
            'pending_schedules' => $schedules->where('status', 'pending')->count(),
            'billed_schedules' => $schedules->where('status', 'billed')->count(),
            'paid_schedules' => $schedules->where('status', 'paid')->count(),
            'overdue_schedules' => $schedules->where('status', 'overdue')->count(),
            'total_scheduled_amount' => $schedules->sum('amount'),
            'paid_amount' => $schedules->where('status', 'paid')->sum('amount'),
            'pending_amount' => $schedules->whereIn('status', ['pending', 'billed'])->sum('amount'),
        ];
    }

    /**
     * Create termin payment schedule for project
     */
    public function createTerminSchedule(array $schedules): bool
    {
        try {
            \DB::beginTransaction();

            // Delete existing schedules if any
            $this->paymentSchedules()->delete();

            $totalPercentage = 0;
            $totalTermin = count($schedules);

            foreach ($schedules as $index => $schedule) {
                $terminNumber = $index + 1;
                $percentage = $schedule['percentage'];
                $totalPercentage += $percentage;

                $this->paymentSchedules()->create([
                    'termin_number' => $terminNumber,
                    'total_termin' => $totalTermin,
                    'termin_name' => $schedule['name'] ?? "Termin {$terminNumber}",
                    'percentage' => $percentage,
                    'amount' => $this->calculateTotalValue() * ($percentage / 100),
                    'due_date' => $schedule['due_date'],
                    'description' => $schedule['description'] ?? null,
                    'status' => 'pending'
                ]);
            }

            // Validate total percentage should be 100%
            if (abs($totalPercentage - 100) > 0.01) {
                throw new \Exception("Total persentase harus 100%, saat ini: {$totalPercentage}%");
            }

            \DB::commit();
            return true;

        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * Get financial summary untuk dashboard
     */
    public function getFinancialSummaryAttribute()
    {
        return [
            'planned_value' => $this->planned_total_value,
            'total_billed' => $this->total_billed_amount,
            'total_expenses' => $this->total_expenses,
            'remaining_billable' => $this->remaining_billable_amount,
            'net_profit' => $this->total_billed_amount - $this->total_expenses,
            'billing_percentage' => $this->billing_percentage
        ];
    }

    /**
     * Scope untuk filter berdasarkan billing status
     */
    public function scopeByBillingStatus($query, string $status)
    {
        return $query->where('billing_status', $status);
    }

    /**
     * Scope untuk proyek yang perlu ditagih
     */
    public function scopeNeedsBilling($query)
    {
        return $query->whereIn('billing_status', ['not_billed', 'partially_billed'])
                    ->where('status', '!=', 'cancelled');
    }

    /**
     * Scope untuk proyek yang sudah selesai tapi belum ditagih penuh
     */
    public function scopeCompletedButNotFullyBilled($query)
    {
        return $query->where('status', 'completed')
                    ->whereIn('billing_status', ['not_billed', 'partially_billed']);
    }

    /**
     * Get all activities related to this project
     */
    public function getAllActivities()
    {
        $activities = collect();

        // 1. Project Activities (aktivitas umum proyek)
        $projectActivities = $this->activities()->with('user')->get()->map(function ($activity) {
            return [
                'id' => $activity->id,
                'type' => 'project_activity',
                'icon' => 'activity',
                'color' => 'blue',
                'title' => 'Aktivitas Proyek',
                'description' => $activity->description,
                'user' => $activity->user->name,
                'user_id' => $activity->user_id,
                'created_at' => $activity->created_at,
                'changes' => $activity->changes,
                'activity_type' => $activity->activity_type
            ];
        });

        // 2. Billing Status Logs (log perubahan status billing batch)
        $billingLogs = \App\Models\BillingStatusLog::whereHas('billingBatch.projectBillings', function ($query) {
            $query->where('project_id', $this->id);
        })->with(['user', 'billingBatch'])->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'type' => 'billing_status',
                'icon' => 'billing',
                'color' => 'green',
                'title' => 'Perubahan Status Billing',
                'description' => "Status billing batch {$log->billingBatch->batch_code} diubah menjadi: {$log->status_label}",
                'user' => $log->user->name ?? 'System',
                'user_id' => $log->user_id,
                'created_at' => $log->created_at,
                'notes' => $log->notes,
                'status' => $log->status,
                'batch_code' => $log->billingBatch->batch_code
            ];
        });

        // 3. Expense Approvals (riwayat persetujuan pengeluaran)
        $expenseApprovals = \App\Models\ExpenseApproval::whereHas('expense', function ($query) {
            $query->where('project_id', $this->id);
        })->with(['approver', 'expense'])->get()->map(function ($approval) {
            $statusLabels = [
                'pending' => 'Menunggu Persetujuan',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak'
            ];
            
            return [
                'id' => $approval->id,
                'type' => 'expense_approval',
                'icon' => 'money',
                'color' => $approval->status === 'approved' ? 'green' : ($approval->status === 'rejected' ? 'red' : 'yellow'),
                'title' => 'Persetujuan Pengeluaran',
                'description' => "Pengeluaran '{$approval->expense->description}' {$statusLabels[$approval->status]} sebesar Rp " . number_format($approval->expense->amount, 0, ',', '.'),
                'user' => $approval->approver->name ?? 'System',
                'user_id' => $approval->approver_id,
                'created_at' => $approval->created_at,
                'notes' => $approval->notes,
                'status' => $approval->status,
                'amount' => $approval->expense->amount
            ];
        });

        // 4. Timeline Updates (perubahan pada milestone timeline)
        $timelineActivities = $this->timelines()->get()->map(function ($timeline) {
            return [
                'id' => $timeline->id,
                'type' => 'timeline_update',
                'icon' => 'calendar',
                'color' => 'purple',
                'title' => 'Update Timeline',
                'description' => "Milestone '{$timeline->milestone}' dibuat dengan status: {$timeline->status}",
                'user' => 'System',
                'user_id' => null,
                'created_at' => $timeline->created_at,
                'milestone' => $timeline->milestone,
                'status' => $timeline->status,
                'progress' => $timeline->progress_percentage
            ];
        });

        // 5. Document Activities (upload/hapus dokumen)
        $documentActivities = $this->documents()->with('uploader')->get()->map(function ($document) {
            return [
                'id' => $document->id,
                'type' => 'document_upload',
                'icon' => 'document',
                'color' => 'indigo',
                'title' => 'Upload Dokumen',
                'description' => "Dokumen '{$document->name}' diunggah ({$document->document_type_label})",
                'user' => $document->uploader->name,
                'user_id' => $document->uploaded_by,
                'created_at' => $document->created_at,
                'document_name' => $document->name,
                'document_type' => $document->document_type
            ];
        });

        // 6. Expense Activities (penambahan pengeluaran baru)
        $expenseActivities = $this->expenses()->with('user')->get()->map(function ($expense) {
            return [
                'id' => $expense->id,
                'type' => 'expense_created',
                'icon' => 'expense',
                'color' => 'orange',
                'title' => 'Pengeluaran Baru',
                'description' => "Pengeluaran '{$expense->description}' ditambahkan sebesar Rp " . number_format($expense->amount, 0, ',', '.'),
                'user' => $expense->user->name,
                'user_id' => $expense->user_id,
                'created_at' => $expense->created_at,
                'amount' => $expense->amount,
                'category' => $expense->category,
                'status' => $expense->status
            ];
        });

        // Gabungkan semua aktivitas
        $activities = $activities->concat($projectActivities)
                               ->concat($billingLogs)
                               ->concat($expenseApprovals)
                               ->concat($timelineActivities)
                               ->concat($documentActivities)
                               ->concat($expenseActivities);

        // Sort berdasarkan created_at descending
        return $activities->sortByDesc('created_at')->take(50);
    }
}
