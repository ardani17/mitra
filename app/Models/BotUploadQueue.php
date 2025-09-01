<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Project;

class BotUploadQueue extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bot_upload_queue';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'telegram_user_id',
        'telegram_username',
        'chat_id',
        'telegram_file_id',
        'file_name',
        'mime_type',
        'file_size',
        'file_type',
        'bot_api_path',
        'project_id',
        'target_folder',
        'status',
        'error_message',
        'retry_count',
        'processed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'telegram_user_id' => 'integer',
        'chat_id' => 'integer',
        'file_size' => 'integer',
        'retry_count' => 'integer',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the project associated with this upload
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope for pending uploads
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for processing uploads
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope for completed uploads
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed uploads
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for uploads that can be retried
     */
    public function scopeRetryable($query, $maxRetries = 3)
    {
        return $query->where('status', 'failed')
                     ->where('retry_count', '<', $maxRetries);
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing()
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Reset for retry
     */
    public function resetForRetry()
    {
        $this->update([
            'status' => 'pending',
            'error_message' => null,
        ]);
    }

    /**
     * Check if can be retried
     */
    public function canRetry($maxRetries = 3)
    {
        return $this->status === 'failed' && $this->retry_count < $maxRetries;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute()
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get file icon based on type
     */
    public function getIconAttribute()
    {
        switch ($this->file_type) {
            case 'document':
                $ext = pathinfo($this->file_name, PATHINFO_EXTENSION);
                switch (strtolower($ext)) {
                    case 'pdf':
                        return 'fas fa-file-pdf text-danger';
                    case 'doc':
                    case 'docx':
                        return 'fas fa-file-word text-primary';
                    case 'xls':
                    case 'xlsx':
                        return 'fas fa-file-excel text-success';
                    case 'ppt':
                    case 'pptx':
                        return 'fas fa-file-powerpoint text-warning';
                    case 'zip':
                    case 'rar':
                    case '7z':
                        return 'fas fa-file-archive text-secondary';
                    default:
                        return 'fas fa-file text-secondary';
                }
            case 'photo':
                return 'fas fa-file-image text-info';
            case 'video':
                return 'fas fa-file-video text-purple';
            default:
                return 'fas fa-file text-secondary';
        }
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'pending':
                return 'badge bg-warning';
            case 'processing':
                return 'badge bg-info';
            case 'completed':
                return 'badge bg-success';
            case 'failed':
                return 'badge bg-danger';
            default:
                return 'badge bg-secondary';
        }
    }

    /**
     * Add a file to the queue
     */
    public static function enqueue($data)
    {
        return self::create(array_merge($data, [
            'status' => 'pending',
            'retry_count' => 0,
        ]));
    }

    /**
     * Get next item to process
     */
    public static function getNextToProcess()
    {
        return self::pending()
            ->orderBy('created_at')
            ->first();
    }

    /**
     * Get queue statistics
     */
    public static function getStatistics()
    {
        return [
            'total' => self::count(),
            'pending' => self::pending()->count(),
            'processing' => self::processing()->count(),
            'completed' => self::completed()->count(),
            'failed' => self::failed()->count(),
            'retryable' => self::retryable()->count(),
            'total_size' => self::sum('file_size'),
            'completed_size' => self::completed()->sum('file_size'),
        ];
    }

    /**
     * Clean old completed items (older than 7 days by default)
     */
    public static function cleanOldCompleted($days = 7)
    {
        return self::completed()
            ->where('processed_at', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * Retry all failed items
     */
    public static function retryAllFailed($maxRetries = 3)
    {
        $items = self::retryable($maxRetries)->get();
        
        foreach ($items as $item) {
            $item->resetForRetry();
        }

        return $items->count();
    }
}