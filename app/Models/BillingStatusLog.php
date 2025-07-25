<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_batch_id',
        'status',
        'notes',
        'user_id'
    ];

    /**
     * Relationship dengan billing batch
     */
    public function billingBatch(): BelongsTo
    {
        return $this->belongsTo(BillingBatch::class);
    }

    /**
     * Relationship dengan user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get status label dalam bahasa Indonesia
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'sent' => 'Terkirim',
            'area_verification' => 'Verifikasi Area',
            'area_revision' => 'Revisi Area',
            'regional_verification' => 'Verifikasi Regional',
            'regional_revision' => 'Revisi Regional',
            'payment_entry_ho' => 'Entry Pembayaran HO',
            'paid' => 'Lunas',
            'cancelled' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $this->status))
        };
    }

    /**
     * Get status color untuk UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'sent' => 'blue',
            'area_verification' => 'yellow',
            'area_revision' => 'orange',
            'regional_verification' => 'purple',
            'regional_revision' => 'red',
            'payment_entry_ho' => 'indigo',
            'paid' => 'green',
            'cancelled' => 'red',
            default => 'gray'
        };
    }
}
