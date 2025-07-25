<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProfitAnalysis extends Model
{
    protected $fillable = [
        'project_id',
        'total_revenue',
        'total_expenses',
        'net_profit',
        'profit_margin',
        'analysis_notes',
        'improvement_recommendations'
    ];

    protected $casts = [
        'total_revenue' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'improvement_recommendations' => 'array'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getFormattedTotalRevenueAttribute()
    {
        return 'Rp ' . number_format($this->total_revenue, 2, ',', '.');
    }

    public function getFormattedTotalExpensesAttribute()
    {
        return 'Rp ' . number_format($this->total_expenses, 2, ',', '.');
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
