<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTimeline extends Model
{
    protected $fillable = [
        'project_id',
        'milestone',
        'description',
        'planned_date',
        'actual_date',
        'status',
        'progress_percentage'
    ];

    protected $casts = [
        'planned_date' => 'date',
        'actual_date' => 'date',
        'progress_percentage' => 'integer'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
