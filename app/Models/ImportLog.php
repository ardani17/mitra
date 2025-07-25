<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    protected $fillable = [
        'user_id',
        'file_name',
        'file_path',
        'status',
        'total_rows',
        'successful_rows',
        'failed_rows',
        'error_message',
        'failed_rows_details',
        'completed_at'
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'successful_rows' => 'integer',
        'failed_rows' => 'integer',
        'failed_rows_details' => 'array',
        'completed_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function getSuccessRateAttribute()
    {
        if ($this->total_rows > 0) {
            return ($this->successful_rows / $this->total_rows) * 100;
        }
        return 0;
    }

    public function getFormattedSuccessRateAttribute()
    {
        return number_format($this->success_rate, 2, ',', '.') . '%';
    }
}
