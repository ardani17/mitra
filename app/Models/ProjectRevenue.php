<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectRevenue extends Model
{
    protected $fillable = [
        'project_id',
        'total_amount',
        'net_profit',
        'profit_margin',
        'revenue_date',
        'calculation_details'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'revenue_date' => 'date'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function revenueItems(): HasMany
    {
        return $this->hasMany(RevenueItem::class, 'revenue_id');
    }

    public function getFormattedTotalAmountAttribute()
    {
        return 'Rp ' . number_format($this->total_amount, 2, ',', '.');
    }

    public function getFormattedNetProfitAttribute()
    {
        return 'Rp ' . number_format($this->net_profit, 2, ',', '.');
    }

    public function getFormattedProfitMarginAttribute()
    {
        return number_format($this->profit_margin, 2, ',', '.') . '%';
    }
}
