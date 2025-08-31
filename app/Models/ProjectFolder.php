<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectFolder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'folder_name',
        'folder_path',
        'parent_id',
        'folder_type',
        'sync_status',
        'metadata'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array'
    ];

    /**
     * Get the project that owns the folder.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the parent folder.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProjectFolder::class, 'parent_id');
    }

    /**
     * Get the child folders.
     */
    public function children(): HasMany
    {
        return $this->hasMany(ProjectFolder::class, 'parent_id');
    }

    /**
     * Get documents in this folder.
     */
    public function documents()
    {
        return $this->hasMany(ProjectDocument::class, 'project_id', 'project_id')
            ->where('file_path', 'like', $this->folder_path . '/%');
    }

    /**
     * Get the file count attribute.
     */
    public function getFileCountAttribute(): int
    {
        return $this->documents()->count();
    }

    /**
     * Get the total size attribute.
     */
    public function getTotalSizeAttribute(): int
    {
        return $this->documents()->sum('file_size');
    }

    /**
     * Get the formatted total size attribute.
     */
    public function getFormattedTotalSizeAttribute(): string
    {
        $bytes = $this->total_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if folder is root.
     */
    public function isRoot(): bool
    {
        return $this->folder_type === 'root';
    }

    /**
     * Get full path from root.
     */
    public function getFullPath(): string
    {
        if ($this->isRoot()) {
            return $this->folder_name;
        }

        $path = [$this->folder_name];
        $parent = $this->parent;
        
        while ($parent && !$parent->isRoot()) {
            array_unshift($path, $parent->folder_name);
            $parent = $parent->parent;
        }
        
        return implode('/', $path);
    }
}