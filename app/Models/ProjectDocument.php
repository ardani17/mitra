<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'uploaded_by',
        'name',
        'original_name',
        'file_path',
        'storage_path',
        'rclone_path',
        'file_type',
        'file_size',
        'document_type',
        'description',
        'sync_status',
        'sync_error',
        'last_sync_at',
        'checksum',
        'folder_structure'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'folder_structure' => 'array',
        'last_sync_at' => 'datetime'
    ];

    /**
     * Relasi ke Project
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relasi ke User (yang upload)
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Format ukuran file menjadi human readable
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get icon berdasarkan file type
     */
    public function getFileIconAttribute(): string
    {
        $extension = strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'pdf':
                return 'text-red-600';
            case 'doc':
            case 'docx':
                return 'text-blue-600';
            case 'xls':
            case 'xlsx':
                return 'text-green-600';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                return 'text-purple-600';
            case 'zip':
            case 'rar':
                return 'text-yellow-600';
            default:
                return 'text-gray-600';
        }
    }

    /**
     * Get document type label
     */
    public function getDocumentTypeLabelAttribute(): string
    {
        return match($this->document_type) {
            'contract' => 'Kontrak',
            'technical' => 'Teknis',
            'financial' => 'Keuangan',
            'report' => 'Laporan',
            'other' => 'Lainnya',
            default => 'Lainnya'
        };
    }

    /**
     * Relasi ke SyncLog (polymorphic)
     */
    public function syncLogs()
    {
        return $this->morphMany(SyncLog::class, 'syncable');
    }

    /**
     * Get file extension attribute
     */
    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }

    /**
     * Check if document is synced
     */
    public function getIsSyncedAttribute(): bool
    {
        return $this->sync_status === 'synced';
    }

    /**
     * Get sync status badge color
     */
    public function getSyncStatusBadgeColorAttribute(): string
    {
        return match($this->sync_status) {
            'synced' => 'bg-green-100 text-green-800',
            'syncing' => 'bg-blue-100 text-blue-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'failed' => 'bg-red-100 text-red-800',
            'out_of_sync' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get sync status label
     */
    public function getSyncStatusLabelAttribute(): string
    {
        return match($this->sync_status) {
            'synced' => 'Synced',
            'syncing' => 'Syncing...',
            'pending' => 'Pending',
            'failed' => 'Failed',
            'out_of_sync' => 'Out of Sync',
            default => 'Unknown'
        };
    }

    /**
     * Scope for documents needing sync
     */
    public function scopeNeedingSync($query)
    {
        return $query->whereIn('sync_status', ['pending', 'failed', 'out_of_sync']);
    }

    /**
     * Scope for synced documents
     */
    public function scopeSynced($query)
    {
        return $query->where('sync_status', 'synced');
    }

    /**
     * Get mime type attribute
     */
    public function getMimeTypeAttribute(): string
    {
        if (isset($this->attributes['mime_type'])) {
            return $this->attributes['mime_type'];
        }
        
        // Fallback to file_type if mime_type not set
        return $this->file_type ?? 'application/octet-stream';
    }
}
