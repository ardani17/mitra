<?php

namespace App\Console\Commands;

use App\Models\BotUser;
use App\Models\BotRole;
use Illuminate\Console\Command;

class AssignBotAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:assign-admin {telegram_id : The Telegram ID of the user} {--role=admin : The role to assign (super_admin, admin, moderator, user, guest)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign admin role to a Telegram bot user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $telegramId = $this->argument('telegram_id');
        $roleName = $this->option('role');
        
        // Find the user
        $user = BotUser::findByTelegramId($telegramId);
        
        if (!$user) {
            $this->error("User with Telegram ID {$telegramId} not found.");
            $this->info("The user needs to register first using /register command in Telegram.");
            return 1;
        }
        
        // Find the role
        $role = BotRole::where('name', $roleName)->first();
        
        if (!$role) {
            $this->error("Role '{$roleName}' not found.");
            $this->info("Available roles: super_admin, admin, moderator, user, guest");
            return 1;
        }
        
        // Get old role for logging
        $oldRole = $user->role;
        
        // Update user role
        $user->role_id = $role->id;
        $user->status = 'active'; // Ensure user is active
        $user->approved_at = $user->approved_at ?: now();
        $user->save();
        
        $this->info("✅ Successfully assigned role '{$role->display_name}' to user:");
        $this->table(
            ['Field', 'Value'],
            [
                ['Telegram ID', $user->telegram_id],
                ['Username', $user->username ?: 'N/A'],
                ['Name', $user->first_name . ' ' . $user->last_name],
                ['Previous Role', $oldRole ? $oldRole->display_name : 'None'],
                ['New Role', $role->display_name],
                ['Status', $user->status],
            ]
        );
        
        // Show role permissions
        $this->info("\nRole Permissions:");
        $permissions = $role->permissions ?? [];
        if (empty($permissions)) {
            $this->line("  No specific permissions defined (full access)");
        } else {
            foreach ($permissions as $permission) {
                $this->line("  • {$permission}");
            }
        }
        
        return 0;
    }
}