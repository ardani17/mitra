<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_name',
        'company_address',
        'company_phone',
        'company_email',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_users');
    }

    public function hasRole($role): bool
    {
        if (is_string($role)) {
            // Load roles if not already loaded
            if (!$this->relationLoaded('roles')) {
                $this->load('roles');
            }
            return $this->roles && $this->roles->contains('name', $role);
        }
        
        if (is_array($role)) {
            return $this->hasAnyRole($role);
        }
        
        return false;
    }

    public function hasAnyRole(array $roles): bool
    {
        // Load roles if not already loaded
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }
        
        if (!$this->roles) {
            return false;
        }
        
        foreach ($roles as $roleName) {
            if ($this->roles->contains('name', $roleName)) {
                return true;
            }
        }
        
        return false;
    }

    public function assignRole($role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }
        
        if ($role) {
            $this->roles()->attach($role);
        }
    }
}
