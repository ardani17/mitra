<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bot_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('display_name', 100);
            $table->text('description')->nullable();
            $table->json('permissions');
            $table->integer('priority')->default(0);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            
            // Index for faster lookups
            $table->index('name');
            $table->index('priority');
        });
        
        // Insert default roles
        $this->insertDefaultRoles();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_roles');
    }
    
    /**
     * Insert default system roles
     */
    private function insertDefaultRoles(): void
    {
        DB::table('bot_roles')->insert([
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Full system access with all permissions',
                'permissions' => json_encode(['*']),
                'priority' => 100,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'User management and system settings',
                'permissions' => json_encode([
                    'users.view', 
                    'users.create', 
                    'users.edit', 
                    'users.delete',
                    'registrations.view', 
                    'registrations.approve', 
                    'registrations.reject',
                    'logs.view', 
                    'settings.view', 
                    'settings.edit',
                    'projects.manage',
                    'files.manage'
                ]),
                'priority' => 90,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'moderator',
                'display_name' => 'Moderator',
                'description' => 'Approve users and moderate content',
                'permissions' => json_encode([
                    'users.view', 
                    'registrations.view', 
                    'registrations.approve', 
                    'registrations.reject', 
                    'logs.view',
                    'projects.view',
                    'files.view'
                ]),
                'priority' => 50,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'Regular user with basic access',
                'permissions' => json_encode([
                    'bot.use', 
                    'projects.view', 
                    'projects.search',
                    'files.upload', 
                    'files.view',
                    'files.download'
                ]),
                'priority' => 10,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'guest',
                'display_name' => 'Guest',
                'description' => 'Limited trial access',
                'permissions' => json_encode([
                    'bot.use',
                    'projects.search',
                    'help.view'
                ]),
                'priority' => 1,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
};
