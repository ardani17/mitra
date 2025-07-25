<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BillingDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_batch_id',
        'stage',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'description',
        'uploaded_by'
    ];

    /**
     * Relationship dengan billing batch
     */
    public function billingBatch(): BelongsTo
    {
        return $this->belongsTo(BillingBatch::class);
    }

    /**
     * Relationship dengan user yang upload
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get stage label dalam bahasa Indonesia
     */
    public function getStageLabelAttribute(): string
    {
        return match($this->stage) {
            'initial' => 'Dokumen Awal',
            'area_revision' => 'Revisi Area',
            'regional_revision' => 'Revisi Regional',
            'supporting_document' => 'Dokumen Pendukung',
            default => ucfirst(str_replace('_', ' ', $this->stage))
        };
    }

    /**
     * Get document type label
     */
    public function getDocumentTypeLabelAttribute(): string
    {
        return match($this->document_type) {
            'invoice' => 'Invoice',
            'tax_invoice' => 'Faktur Pajak',
            'sp' => 'Surat Penagihan',
            'supporting' => 'Dokumen Pendukung',
            default => ucfirst($this->document_type ?? 'Dokumen')
        };
    }

    /**
     * Get file URL
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '-';
        }

        $bytes = (int) $this->file_size;
        
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
     * Check if file is image
     */
    public function getIsImageAttribute(): bool
    {
        return in_array($this->mime_type, [
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'image/gif',
            'image/webp'
        ]);
    }

    /**
     * Check if file is PDF
     */
    public function getIsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Get file icon based on mime type
     */
    public function getFileIconAttribute(): string
    {
        if ($this->is_image) {
            return 'photo';
        } elseif ($this->is_pdf) {
            return 'document-text';
        } elseif (str_contains($this->mime_type, 'word')) {
            return 'document';
        } elseif (str_contains($this->mime_type, 'excel') || str_contains($this->mime_type, 'spreadsheet')) {
            return 'table';
        } else {
            return 'document';
        }
    }

    /**
     * Delete file from storage when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($document) {
            if (Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }
        });
    }
}
