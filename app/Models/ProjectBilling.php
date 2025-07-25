<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBilling extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id',
        'billing_batch_id',
        'base_amount',
        'pph_amount',
        'received_amount',
        'nilai_jasa',
        'nilai_material',
        'subtotal',
        'ppn_rate',
        'ppn_calculation',
        'ppn_amount',
        'total_amount',
        'invoice_number',
        'sp_number',
        'tax_invoice_number',
        'billing_date',
        'paid_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'pph_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'nilai_jasa' => 'decimal:2',
        'nilai_material' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'ppn_rate' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'billing_date' => 'date',
        'paid_date' => 'date'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function billingBatch(): BelongsTo
    {
        return $this->belongsTo(BillingBatch::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Calculate subtotal from nilai_jasa + nilai_material
     */
    public function calculateSubtotal(): float
    {
        return $this->nilai_jasa + $this->nilai_material;
    }

    /**
     * Calculate PPN amount based on subtotal and rate
     */
    public function calculatePpnAmount(): float
    {
        $subtotal = $this->calculateSubtotal();
        $ppnAmount = $subtotal * ($this->ppn_rate / 100);

        switch ($this->ppn_calculation) {
            case 'round_down':
                return floor($ppnAmount);
            case 'round_up':
                return ceil($ppnAmount);
            default:
                return round($ppnAmount, 2);
        }
    }

    /**
     * Calculate total amount (subtotal + PPN)
     */
    public function calculateTotalAmount(): float
    {
        return $this->calculateSubtotal() + $this->calculatePpnAmount();
    }

    /**
     * Auto-calculate and update all amounts
     */
    public function updateCalculatedAmounts(): void
    {
        $this->subtotal = $this->calculateSubtotal();
        $this->ppn_amount = $this->calculatePpnAmount();
        $this->total_amount = $this->calculateTotalAmount();
    }

    /**
     * Generate invoice number
     */
    public function generateInvoiceNumber(): string
    {
        if ($this->invoice_number) {
            return $this->invoice_number;
        }

        $year = $this->billing_date->format('Y');
        $month = $this->billing_date->format('m');
        
        // Get last invoice number for this month
        $lastInvoice = static::whereYear('billing_date', $year)
            ->whereMonth('billing_date', $month)
            ->whereNotNull('invoice_number')
            ->orderBy('invoice_number', 'desc')
            ->first();

        $sequence = 1;
        if ($lastInvoice && $lastInvoice->invoice_number) {
            // Extract sequence from last invoice number (format: INV/YYYY/MM/XXX)
            $parts = explode('/', $lastInvoice->invoice_number);
            if (count($parts) === 4) {
                $sequence = intval($parts[3]) + 1;
            }
        }

        return sprintf('INV/%s/%s/%03d', $year, $month, $sequence);
    }

    /**
     * Boot method to auto-calculate amounts
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($billing) {
            // Only calculate if we have the required fields
            if ($billing->nilai_jasa !== null && $billing->nilai_material !== null) {
                $billing->updateCalculatedAmounts();
            }
            
            if (!$billing->invoice_number && $billing->status !== 'draft' && $billing->billing_date) {
                $billing->invoice_number = $billing->generateInvoiceNumber();
            }
        });
    }
}
