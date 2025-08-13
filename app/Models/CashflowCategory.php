<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashflowCategory extends Model
{
    protected $fillable = [
        'name',
        'type',
        'code',
        'description',
        'is_active',
        'is_system'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean'
    ];

    /**
     * Get cashflow entries for this category
     */
    public function cashflowEntries(): HasMany
    {
        return $this->hasMany(CashflowEntry::class, 'category_id');
    }

    /**
     * Scope untuk kategori aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk kategori income
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope untuk kategori expense
     */
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope untuk kategori sistem
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope untuk kategori non-sistem (bisa dihapus)
     */
    public function scopeNonSystem($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Check if category can be deleted
     */
    public function canBeDeleted(): bool
    {
        return !$this->is_system && $this->cashflowEntries()->count() === 0;
    }

    /**
     * Get formatted type
     */
    public function getFormattedTypeAttribute(): string
    {
        return $this->type === 'income' ? 'Pemasukan' : 'Pengeluaran';
    }

    /**
     * Get default categories for specific types
     */
    public static function getDefaultCategories(string $type = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = static::active();
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->orderBy('name')->get();
    }

    /**
     * Get system category by code
     */
    public static function getSystemCategory(string $code): ?self
    {
        return static::where('code', $code)
            ->where('is_system', true)
            ->where('is_active', true)
            ->first();
    }
}
