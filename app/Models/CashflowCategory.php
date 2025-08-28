<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashflowCategory extends Model
{
    protected $fillable = [
        'name',
        'type',
        'group',
        'code',
        'description',
        'is_active',
        'is_system',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Group labels for display
     */
    public static $groupLabels = [
        // Income groups
        'proyek' => 'Proyek',
        'hutang_modal' => 'Hutang & Modal',
        'piutang_tagihan' => 'Piutang & Tagihan',
        'pendapatan_lain' => 'Pendapatan Lainnya',
        
        // Expense groups
        'hutang_pinjaman' => 'Hutang & Pinjaman',
        'operasional' => 'Operasional',
        'aset_investasi' => 'Aset & Investasi',
        'pengeluaran_lain' => 'Pengeluaran Lainnya',
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
     * Scope untuk filter by group
     */
    public function scopeInGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope untuk ordering
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
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
     * Get formatted group label
     */
    public function getFormattedGroupAttribute(): string
    {
        return self::$groupLabels[$this->group] ?? ucfirst(str_replace('_', ' ', $this->group));
    }

    /**
     * Get icon for category group
     */
    public function getGroupIconAttribute(): string
    {
        $icons = [
            'proyek' => 'fa-building',
            'hutang_modal' => 'fa-hand-holding-usd',
            'piutang_tagihan' => 'fa-file-invoice-dollar',
            'pendapatan_lain' => 'fa-coins',
            'hutang_pinjaman' => 'fa-credit-card',
            'operasional' => 'fa-cogs',
            'aset_investasi' => 'fa-chart-line',
            'pengeluaran_lain' => 'fa-receipt',
        ];

        return $icons[$this->group] ?? 'fa-folder';
    }

    /**
     * Get color class for category type
     */
    public function getTypeColorClassAttribute(): string
    {
        return $this->type === 'income' ? 'text-green-600' : 'text-red-600';
    }

    /**
     * Get badge class for category type
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return $this->type === 'income' 
            ? 'bg-green-100 text-green-800' 
            : 'bg-red-100 text-red-800';
    }

    /**
     * Get default categories for specific types
     */
    public static function getDefaultCategories(string $type = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = static::active()->ordered();
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->get();
    }

    /**
     * Get categories grouped by their group field
     */
    public static function getCategoriesGrouped(string $type = null): \Illuminate\Support\Collection
    {
        $query = static::active()->ordered();
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->get()->groupBy('group')->map(function ($group) {
            return $group->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'code' => $category->code,
                    'description' => $category->description,
                    'type' => $category->type,
                    'group' => $category->group,
                    'formatted_group' => $category->formatted_group,
                    'is_system' => $category->is_system,
                ];
            });
        });
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

    /**
     * Get statistics for categories
     */
    public static function getStatistics(): array
    {
        $stats = [];
        
        // Total by type
        $stats['total_income_categories'] = static::active()->income()->count();
        $stats['total_expense_categories'] = static::active()->expense()->count();
        
        // Total by group
        $stats['by_group'] = static::active()
            ->selectRaw('`group`, type, COUNT(*) as total')
            ->groupBy('group', 'type')
            ->get()
            ->groupBy('type')
            ->map(function ($types) {
                return $types->pluck('total', 'group');
            })
            ->toArray();
        
        // Categories with most entries
        $stats['most_used'] = static::active()
            ->withCount('cashflowEntries')
            ->orderBy('cashflow_entries_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'type' => $category->type,
                    'count' => $category->cashflow_entries_count,
                ];
            });
        
        return $stats;
    }
}
