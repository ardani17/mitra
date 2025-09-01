<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotRole extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'priority',
        'is_system',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Get the users for the role.
     */
    public function users()
    {
        return $this->hasMany(BotUser::class, 'role_id');
    }

    /**
     * Check if the role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];
        
        // Check for wildcard permission
        if (in_array('*', $permissions)) {
            return true;
        }

        // Check specific permission
        return in_array($permission, $permissions);
    }

    /**
     * Add a permission to the role.
     */
    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }
    }

    /**
     * Remove a permission from the role.
     */
    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        
        $permissions = array_values(array_filter($permissions, function ($p) use ($permission) {
            return $p !== $permission;
        }));
        
        $this->permissions = $permissions;
        $this->save();
    }

    /**
     * Set multiple permissions at once.
     */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = array_values(array_unique($permissions));
        $this->save();
    }

    /**
     * Check if this is a system role (cannot be deleted).
     */
    public function isSystem(): bool
    {
        return $this->is_system;
    }

    /**
     * Check if this role is higher priority than another role.
     */
    public function isHigherThan(BotRole $otherRole): bool
    {
        return $this->priority > $otherRole->priority;
    }

    /**
     * Get role by name.
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /**
     * Get the super admin role.
     */
    public static function superAdmin(): ?self
    {
        return static::findByName('super_admin');
    }

    /**
     * Get the admin role.
     */
    public static function admin(): ?self
    {
        return static::findByName('admin');
    }

    /**
     * Get the moderator role.
     */
    public static function moderator(): ?self
    {
        return static::findByName('moderator');
    }

    /**
     * Get the default user role.
     */
    public static function user(): ?self
    {
        return static::findByName('user');
    }

    /**
     * Get the guest role.
     */
    public static function guest(): ?self
    {
        return static::findByName('guest');
    }

    /**
     * Get all available permissions from all roles.
     */
    public static function getAllPermissions(): array
    {
        $allPermissions = [];
        
        $roles = static::all();
        foreach ($roles as $role) {
            $permissions = $role->permissions ?? [];
            $allPermissions = array_merge($allPermissions, $permissions);
        }
        
        // Remove duplicates and wildcards
        $allPermissions = array_unique(array_filter($allPermissions, function ($p) {
            return $p !== '*';
        }));
        
        return array_values($allPermissions);
    }

    /**
     * Get roles ordered by priority.
     */
    public static function byPriority()
    {
        return static::orderBy('priority', 'desc');
    }
}