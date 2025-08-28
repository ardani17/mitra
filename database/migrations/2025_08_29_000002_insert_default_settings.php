<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            // Expense Settings
            [
                'key' => 'expense_director_bypass_enabled',
                'value' => '0',
                'description' => 'Enable director to bypass expense approval workflow when creating expenses',
                'type' => 'boolean',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'expense_approval_notification_enabled',
                'value' => '1',
                'description' => 'Send email notifications for expense approvals',
                'type' => 'boolean',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'expense_high_amount_threshold',
                'value' => '10000000',
                'description' => 'Amount threshold for high-value expenses requiring director approval (in Rupiah)',
                'type' => 'integer',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Salary Cut-off Settings
            [
                'key' => 'salary_cutoff_start_day',
                'value' => '11',
                'description' => 'Tanggal mulai periode gaji (1-31)',
                'type' => 'integer',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'salary_cutoff_end_day',
                'value' => '10',
                'description' => 'Tanggal akhir periode gaji (1-31)',
                'type' => 'integer',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'salary_status_complete_threshold',
                'value' => '90',
                'description' => 'Persentase minimum untuk status lengkap (%)',
                'type' => 'integer',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'salary_status_partial_threshold',
                'value' => '50',
                'description' => 'Persentase minimum untuk status kurang (%)',
                'type' => 'integer',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'salary_status_auto_refresh',
                'value' => '1',
                'description' => 'Auto refresh status setiap 5 menit (0=off, 1=on)',
                'type' => 'boolean',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'salary_status_email_notification',
                'value' => '1',
                'description' => 'Email notification untuk status rendah (0=off, 1=on)',
                'type' => 'boolean',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($settings as $setting) {
            // Check if setting doesn't exist before inserting
            if (!DB::table('settings')->where('key', $setting['key'])->exists()) {
                DB::table('settings')->insert($setting);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't delete settings on rollback as they might have been modified
        // This is a safety measure to prevent losing configuration
    }
};