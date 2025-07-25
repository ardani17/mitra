<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    /**
     * Menambahkan atau mengupdate client
     */
    public static function addOrUpdateClient($clientName)
    {
        if (empty($clientName)) {
            return;
        }

        $client = self::where('name', $clientName)->first();

        if ($client) {
            // Update usage count dan last used
            $client->increment('usage_count');
            $client->update(['last_used_at' => now()]);
        } else {
            // Buat client baru
            self::create([
                'name' => $clientName,
                'usage_count' => 1,
                'last_used_at' => now(),
            ]);
        }
    }

    /**
     * Mendapatkan client populer berdasarkan usage count dan last used
     */
    public static function getPopularClients($limit = 10)
    {
        return self::orderBy('usage_count', 'desc')
                   ->orderBy('last_used_at', 'desc')
                   ->limit($limit)
                   ->pluck('name')
                   ->toArray();
    }

    /**
     * Mencari client berdasarkan nama
     */
    public static function searchClients($search, $limit = 10)
    {
        return self::where('name', 'ILIKE', "%{$search}%")
                   ->orderBy('usage_count', 'desc')
                   ->orderBy('last_used_at', 'desc')
                   ->limit($limit)
                   ->pluck('name')
                   ->toArray();
    }

    /**
     * Mendapatkan semua client yang unik
     */
    public static function getAllClients()
    {
        return self::orderBy('name')
                   ->pluck('name')
                   ->toArray();
    }

    /**
     * Membersihkan client yang tidak digunakan lagi
     */
    public static function cleanupUnusedClients($daysOld = 365)
    {
        return self::where('last_used_at', '<', now()->subDays($daysOld))
                   ->where('usage_count', 1)
                   ->delete();
    }
}
