<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SettingController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the settings page
     */
    public function index()
    {
        if (!Auth::user() || !Auth::user()->hasRole('direktur')) {
            abort(403, 'Hanya direktur yang dapat mengakses pengaturan sistem.');
        }

        $settings = [
            'expense_director_bypass_enabled' => Setting::isDirectorBypassEnabled(),
            'expense_approval_notification_enabled' => Setting::get('expense_approval_notification_enabled', true),
            'expense_high_amount_threshold' => Setting::get('expense_high_amount_threshold', 10000000),
            // Salary cut-off settings
            'salary_cutoff_start_day' => Setting::get('salary_cutoff_start_day', 11),
            'salary_cutoff_end_day' => Setting::get('salary_cutoff_end_day', 10),
            'salary_status_complete_threshold' => Setting::get('salary_status_complete_threshold', 90),
            'salary_status_partial_threshold' => Setting::get('salary_status_partial_threshold', 50),
        ];

        return view('settings.index', compact('settings'));
    }

    /**
     * Update director bypass setting
     */
    public function updateDirectorBypass(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean'
        ]);

        $enabled = $request->boolean('enabled');
        Setting::setDirectorBypass($enabled);

        // Log the setting change
        \Log::info('Director bypass setting changed', [
            'changed_by' => Auth::id(),
            'new_value' => $enabled,
            'timestamp' => now()
        ]);

        $message = $enabled 
            ? 'Fitur bypass approval direktur telah diaktifkan.' 
            : 'Fitur bypass approval direktur telah dinonaktifkan.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update notification setting
     */
    public function updateNotificationSetting(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean'
        ]);

        $enabled = $request->boolean('enabled');
        Setting::set(
            'expense_approval_notification_enabled',
            $enabled,
            'Send email notifications for expense approvals',
            'boolean'
        );

        $message = $enabled 
            ? 'Notifikasi email approval telah diaktifkan.' 
            : 'Notifikasi email approval telah dinonaktifkan.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update high amount threshold
     */
    public function updateHighAmountThreshold(Request $request)
    {
        $request->validate([
            'threshold' => 'required|numeric|min:1000000|max:1000000000'
        ]);

        $threshold = $request->input('threshold');
        Setting::set(
            'expense_high_amount_threshold',
            $threshold,
            'Amount threshold for high-value expenses requiring director approval (in Rupiah)',
            'integer'
        );

        // Log the setting change
        \Log::info('High amount threshold changed', [
            'changed_by' => Auth::id(),
            'new_value' => $threshold,
            'timestamp' => now()
        ]);

        return redirect()->back()->with('success', 'Batas nilai tinggi untuk approval direktur telah diperbarui.');
    }

    /**
     * Get current settings as JSON (for API)
     */
    public function getSettings()
    {
        $settings = [
            'expense_director_bypass_enabled' => Setting::isDirectorBypassEnabled(),
            'expense_approval_notification_enabled' => Setting::get('expense_approval_notification_enabled', true),
            'expense_high_amount_threshold' => Setting::get('expense_high_amount_threshold', 10000000)
        ];

        return response()->json($settings);
    }

    /**
     * Reset all settings to default
     */
    public function resetToDefault()
    {
        Setting::setDirectorBypass(false);
        Setting::set('expense_approval_notification_enabled', true, 'Send email notifications for expense approvals', 'boolean');
        Setting::set('expense_high_amount_threshold', 10000000, 'Amount threshold for high-value expenses requiring director approval (in Rupiah)', 'integer');

        // Log the reset action
        \Log::info('Settings reset to default', [
            'reset_by' => Auth::id(),
            'timestamp' => now()
        ]);

        return redirect()->back()->with('success', 'Semua pengaturan telah dikembalikan ke nilai default.');
    }

    /**
     * Update salary cutoff settings
     */
    public function updateSalaryCutoff(Request $request)
    {
        if (!Auth::user() || !Auth::user()->hasRole('direktur')) {
            abort(403, 'Hanya direktur yang dapat mengubah pengaturan sistem.');
        }

        $validated = $request->validate([
            'salary_cutoff_start_day' => 'required|integer|min:1|max:31',
            'salary_cutoff_end_day' => 'required|integer|min:1|max:31',
            'salary_status_complete_threshold' => 'required|integer|min:1|max:100',
            'salary_status_partial_threshold' => 'required|integer|min:1|max:100',
        ]);

        // Validate that partial threshold is less than complete threshold
        if ($validated['salary_status_partial_threshold'] >= $validated['salary_status_complete_threshold']) {
            return redirect()->back()->with('error', 'Threshold "Kurang" harus lebih kecil dari threshold "Lengkap".');
        }

        try {
            foreach ($validated as $key => $value) {
                Setting::set($key, $value, $this->getSalarySettingDescription($key), 'integer');
            }

            // Log the setting change
            \Log::info('Salary cutoff settings changed', [
                'changed_by' => Auth::id(),
                'new_values' => $validated,
                'timestamp' => now()
            ]);

            return redirect()->back()->with('success', 'Pengaturan periode gaji berhasil diperbarui.');
        } catch (\Exception $e) {
            \Log::error('Failed to update salary cutoff settings', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return redirect()->back()->with('error', 'Gagal memperbarui pengaturan: ' . $e->getMessage());
        }
    }

    /**
     * Get description for salary settings
     */
    private function getSalarySettingDescription($key)
    {
        $descriptions = [
            'salary_cutoff_start_day' => 'Tanggal mulai periode gaji (1-31)',
            'salary_cutoff_end_day' => 'Tanggal akhir periode gaji (1-31)',
            'salary_status_complete_threshold' => 'Persentase minimum untuk status lengkap (%)',
            'salary_status_partial_threshold' => 'Persentase minimum untuk status kurang (%)',
        ];

        return $descriptions[$key] ?? '';
    }
}
