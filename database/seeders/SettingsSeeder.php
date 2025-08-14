<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'expense_director_bypass_enabled',
                'value' => '0',
                'description' => 'Enable director to bypass expense approval workflow when creating expenses',
                'type' => 'boolean'
            ],
            [
                'key' => 'expense_approval_notification_enabled',
                'value' => '1',
                'description' => 'Send email notifications for expense approvals',
                'type' => 'boolean'
            ],
            [
                'key' => 'expense_high_amount_threshold',
                'value' => '10000000',
                'description' => 'Amount threshold for high-value expenses requiring director approval (in Rupiah)',
                'type' => 'integer'
            ],
            // Salary Cut-off Settings
            [
                'key' => 'salary_cutoff_start_day',
                'value' => '11',
                'description' => 'Tanggal mulai periode gaji (1-31)',
                'type' => 'integer'
            ],
            [
                'key' => 'salary_cutoff_end_day',
                'value' => '10',
                'description' => 'Tanggal akhir periode gaji (1-31)',
                'type' => 'integer'
            ],
            [
                'key' => 'salary_status_complete_threshold',
                'value' => '90',
                'description' => 'Persentase minimum untuk status lengkap (%)',
                'type' => 'integer'
            ],
            [
                'key' => 'salary_status_partial_threshold',
                'value' => '50',
                'description' => 'Persentase minimum untuk status kurang (%)',
                'type' => 'integer'
            ],
            [
                'key' => 'salary_status_auto_refresh',
                'value' => '1',
                'description' => 'Auto refresh status setiap 5 menit (0=off, 1=on)',
                'type' => 'boolean'
            ],
            [
                'key' => 'salary_status_email_notification',
                'value' => '1',
                'description' => 'Email notification untuk status rendah (0=off, 1=on)',
                'type' => 'boolean'
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'description' => $setting['description'],
                    'type' => $setting['type']
                ]
            );
        }

        $this->command->info('Settings seeded successfully!');
    }
}
