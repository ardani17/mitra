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
        'file_type',
        'file_size',
        'document_type',
        'description',
    ];

    protected $casts = [
        'file_size' => 'integer',
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
}
