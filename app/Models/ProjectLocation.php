<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    /**
     * Scope untuk mencari lokasi berdasarkan nama
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'LIKE', "%{$search}%");
    }

    /**
     * Scope untuk mengurutkan berdasarkan popularitas (usage_count)
     */
    public function scopePopular($query)
    {
        return $query->orderBy('usage_count', 'desc');
    }

    /**
     * Scope untuk mengurutkan berdasarkan penggunaan terakhir
     */
    public function scopeRecentlyUsed($query)
    {
        return $query->orderBy('last_used_at', 'desc');
    }

    /**
     * Method untuk menambah atau update lokasi
     */
    public static function addOrUpdateLocation($locationName, $description = null)
    {
        if (empty(trim($locationName))) {
            return null;
        }

        $location = self::where('name', $locationName)->first();

        if ($location) {
            // Update existing location
            $location->increment('usage_count');
            $location->update([
                'last_used_at' => now(),
                'description' => $description ?? $location->description
            ]);
        } else {
            // Create new location
            $location = self::create([
                'name' => $locationName,
                'description' => $description,
                'usage_count' => 1,
                'last_used_at' => now()
            ]);
        }

        return $location;
    }

    /**
     * Method untuk mendapatkan lokasi populer untuk autocomplete
     */
    public static function getPopularLocations($limit = 10)
    {
        return self::popular()
            ->limit($limit)
            ->pluck('name')
            ->toArray();
    }

    /**
     * Method untuk mencari lokasi untuk autocomplete
     */
    public static function searchLocations($search, $limit = 10)
    {
        return self::search($search)
            ->popular()
            ->limit($limit)
            ->pluck('name')
            ->toArray();
    }
}
