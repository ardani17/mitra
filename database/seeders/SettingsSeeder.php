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
