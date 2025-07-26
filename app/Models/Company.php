<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'contact_person',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the active company
     */
    public static function getActive()
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Set this company as active and deactivate others
     */
    public function setAsActive()
    {
        // Deactivate all companies
        static::query()->update(['is_active' => false]);
        
        // Activate this company
        $this->update(['is_active' => true]);
    }
}
