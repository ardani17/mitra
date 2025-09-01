<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\BotConfiguration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing allowed users from bot_configurations to bot_users
        $this->migrateExistingUsers();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as it migrates data
        // We don't want to delete users that might have been added after migration
    }
    
    /**
     * Migrate existing allowed users from the old system
     */
    private function migrateExistingUsers(): void
    {
        try {
            // Get the active bot configuration
            $config = DB::table('bot_configurations')
                ->where('is_active', true)
                ->first();
            
            if (!$config || !$config->allowed_users) {
                return;
            }
            
            // Decode the allowed_users JSON
            $allowedUsers = json_decode($config->allowed_users, true);
            
            if (!is_array($allowedUsers)) {
                return;
            }
            
            // Get the default user role ID
            $userRole = DB::table('bot_roles')
                ->where('name', 'user')
                ->first();
            
            if (!$userRole) {
                // If user role doesn't exist, use ID 4 (default user role)
                $userRoleId = 4;
            } else {
                $userRoleId = $userRole->id;
            }
            
            // Get the admin role ID for the first user (if any)
            $adminRole = DB::table('bot_roles')
                ->where('name', 'admin')
                ->first();
            
            $adminRoleId = $adminRole ? $adminRole->id : 2;
            
            $isFirstUser = true;
            
            foreach ($allowedUsers as $index => $userInfo) {
                // Handle different formats of allowed_users
                $telegramId = null;
                $username = null;
                
                if (is_array($userInfo)) {
                    // New format: ['id' => 123456, 'username' => 'john', 'added_at' => '...']
                    $telegramId = $userInfo['id'] ?? null;
                    $username = $userInfo['username'] ?? null;
                } else {
                    // Old format: just the telegram ID as a value
                    $telegramId = $userInfo;
                }
                
                if (!$telegramId) {
                    continue;
                }
                
                // Check if user already exists in bot_users
                $existingUser = DB::table('bot_users')
                    ->where('telegram_id', $telegramId)
                    ->first();
                
                if ($existingUser) {
                    continue;
                }
                
                // Insert the user
                DB::table('bot_users')->insert([
                    'telegram_id' => $telegramId,
                    'username' => $username,
                    'first_name' => $username ?? 'User',
                    'last_name' => null,
                    'role_id' => $isFirstUser ? $adminRoleId : $userRoleId, // First user becomes admin
                    'status' => 'active',
                    'registered_at' => now(),
                    'approved_at' => now(),
                    'approved_by' => null,
                    'last_active_at' => null,
                    'metadata' => json_encode([
                        'migrated_from_old_system' => true,
                        'migration_date' => now()->toIso8601String()
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Log the migration
                DB::table('bot_user_activity_logs')->insert([
                    'user_id' => DB::getPdo()->lastInsertId(),
                    'telegram_id' => $telegramId,
                    'action' => 'user_migrated',
                    'details' => json_encode([
                        'source' => 'bot_configurations.allowed_users',
                        'original_data' => $userInfo
                    ]),
                    'status' => 'success',
                    'created_at' => now(),
                ]);
                
                $isFirstUser = false;
            }
            
            // Log migration completion
            \Log::info('Successfully migrated ' . count($allowedUsers) . ' users from old system to new bot_users table');
            
        } catch (\Exception $e) {
            \Log::error('Error migrating existing allowed users: ' . $e->getMessage());
            // Don't throw exception to prevent migration failure
            // The admin can manually add users if migration fails
        }
    }
};
