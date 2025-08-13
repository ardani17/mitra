<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class AssignUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:assign-role {user_id} {role_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a role to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $roleName = $this->argument('role_name');

        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error("Role '{$roleName}' not found.");
            return 1;
        }

        // Check if user already has this role
        if ($user->roles()->where('role_id', $role->id)->exists()) {
            $this->info("User '{$user->name}' already has role '{$roleName}'.");
            return 0;
        }

        // Assign role
        $user->roles()->attach($role->id);
        
        $this->info("Role '{$roleName}' assigned to user '{$user->name}' successfully.");
        return 0;
    }
}