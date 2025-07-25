<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevenueItem extends Model
{
    protected $fillable = [
        'project_id',
        'revenue_id',
        'item_name',
        'description',
        'amount',
        'type'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function revenue(): BelongsTo
    {
        return $this->belongsTo(ProjectRevenue::class, 'revenue_id');
    }
}
