<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class BillingBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_code',
        'invoice_number',
        'tax_invoice_number',
        'sp_number',
        'total_base_amount',
        'pph_rate',
        'pph_amount',
        'ppn_rate',
        'ppn_amount',
        'total_billing_amount',
        'total_received_amount',
        'status',
        'client_type',
        'billing_date',
        'sent_date',
        'area_verification_date',
        'area_revision_date',
        'regional_verification_date',
        'regional_revision_date',
        'payment_entry_date',
        'paid_date',
        'notes'
    ];

    protected $casts = [
        'billing_date' => 'date',
        'sent_date' => 'datetime',
        'area_verification_date' => 'datetime',
        'area_revision_date' => 'datetime',
        'regional_verification_date' => 'datetime',
        'regional_revision_date' => 'datetime',
        'payment_entry_date' => 'datetime',
        'paid_date' => 'datetime',
        'total_base_amount' => 'decimal:2',
        'pph_rate' => 'decimal:2',
        'pph_amount' => 'decimal:2',
        'ppn_rate' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'total_billing_amount' => 'decimal:2',
        'total_received_amount' => 'decimal:2',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_AREA_VERIFICATION = 'area_verification';
    const STATUS_AREA_REVISION = 'area_revision';
    const STATUS_REGIONAL_VERIFICATION = 'regional_verification';
    const STATUS_REGIONAL_REVISION = 'regional_revision';
    const STATUS_PAYMENT_ENTRY_HO = 'payment_entry_ho';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Relationship dengan project billings
     */
    public function projectBillings(): HasMany
    {
        return $this->hasMany(ProjectBilling::class);
    }

    /**
     * Relationship dengan status logs
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(BillingStatusLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Relationship dengan documents
     */
    public function documents(): HasMany
    {
        return $this->hasMany(BillingDocument::class);
    }

    /**
     * Generate batch code otomatis
     */
    public static function generateBatchCode(): string
    {
        $year = date('Y');
        $month = date('m');
        
        $lastBatch = self::where('batch_code', 'like', "BTH-{$year}-{$month}-%")
                        ->orderBy('batch_code', 'desc')
                        ->first();
        
        if ($lastBatch) {
            $lastNumber = (int) substr($lastBatch->batch_code, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return sprintf('BTH-%s-%s-%03d', $year, $month, $newNumber);
    }

    /**
     * Calculate tax amounts berdasarkan total base amount
     */
    public function calculateTaxAmounts(): void
    {
        $this->ppn_amount = ($this->total_base_amount * $this->ppn_rate) / 100;
        $this->pph_amount = ($this->total_base_amount * $this->pph_rate) / 100;
        $this->total_billing_amount = $this->total_base_amount + $this->ppn_amount;
        $this->total_received_amount = $this->total_billing_amount - $this->pph_amount;
    }

    /**
     * Update status dan log perubahan
     */
    public function updateStatus(string $newStatus, string $notes = null, int $userId = null): void
    {
        $oldStatus = $this->status;
        $this->status = $newStatus;
        
        // Update tanggal sesuai status
        $this->updateStatusDate($newStatus);
        
        $this->save();
        
        // Log perubahan status
        $this->statusLogs()->create([
            'status' => $newStatus,
            'notes' => $notes,
            'user_id' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Update tanggal sesuai status
     */
    private function updateStatusDate(string $status): void
    {
        $now = now();
        
        switch ($status) {
            case self::STATUS_SENT:
                $this->sent_date = $now;
                break;
            case self::STATUS_AREA_VERIFICATION:
                $this->area_verification_date = $now;
                break;
            case self::STATUS_AREA_REVISION:
                $this->area_revision_date = $now;
                break;
            case self::STATUS_REGIONAL_VERIFICATION:
                $this->regional_verification_date = $now;
                break;
            case self::STATUS_REGIONAL_REVISION:
                $this->regional_revision_date = $now;
                break;
            case self::STATUS_PAYMENT_ENTRY_HO:
                $this->payment_entry_date = $now;
                break;
            case self::STATUS_PAID:
                $this->paid_date = $now;
                break;
        }
    }

    /**
     * Get status label dalam bahasa Indonesia
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SENT => 'Terkirim',
            self::STATUS_AREA_VERIFICATION => 'Verifikasi Area',
            self::STATUS_AREA_REVISION => 'Revisi Area',
            self::STATUS_REGIONAL_VERIFICATION => 'Verifikasi Regional',
            self::STATUS_REGIONAL_REVISION => 'Revisi Regional',
            self::STATUS_PAYMENT_ENTRY_HO => 'Entry Pembayaran HO',
            self::STATUS_PAID => 'Lunas',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $this->status))
        };
    }

    /**
     * Get status color untuk UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_SENT => 'blue',
            self::STATUS_AREA_VERIFICATION => 'yellow',
            self::STATUS_AREA_REVISION => 'orange',
            self::STATUS_REGIONAL_VERIFICATION => 'purple',
            self::STATUS_REGIONAL_REVISION => 'red',
            self::STATUS_PAYMENT_ENTRY_HO => 'indigo',
            self::STATUS_PAID => 'green',
            self::STATUS_CANCELLED => 'red',
            default => 'gray'
        };
    }

    /**
     * Get aging dalam hari dari status terakhir
     */
    public function getAgingDaysAttribute(): int
    {
        $lastStatusLog = $this->statusLogs()->first();
        
        if (!$lastStatusLog) {
            return $this->created_at->diffInDays(now());
        }
        
        return $lastStatusLog->created_at->diffInDays(now());
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk aging report
     */
    public function scopeAging($query, int $days)
    {
        return $query->where('created_at', '<=', now()->subDays($days));
    }
}
