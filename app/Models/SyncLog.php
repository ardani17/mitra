<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SyncLog extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'syncable_type',
        'syncable_id',
        'action',
        'status',
        'source_path',
        'destination_path',
        'file_size',
        'duration_ms',
        'error_message',
        'rclone_output'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'file_size' => 'integer',
        'duration_ms' => 'integer'
    ];

    /**
     * Get the parent syncable model (Project or ProjectDocument).
     */
    public function syncable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the formatted duration attribute.
     */
    public function getFormattedDurationAttribute(): string
    {
        $ms = $this->duration_ms;
        
        if ($ms < 1000) {
            return $ms . 'ms';
        }
        
        $seconds = $ms / 1000;
        if ($seconds < 60) {
            return round($seconds, 2) . 's';
        }
        
        $minutes = $seconds / 60;
        return round($minutes, 2) . 'm';
    }

    /**
     * Get the formatted file size attribute.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '0 B';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Scope a query to only include successful syncs.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope a query to only include failed syncs.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include recent logs.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'success' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'skipped' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'upload' => 'Upload',
            'download' => 'Download',
            'delete' => 'Delete',
            'check' => 'Check',
            default => ucfirst($this->action)
        };
    }
}