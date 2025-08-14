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
            'expense_high_amount_threshold' => Setting::get('expense_high_amount_threshold', 10000000)
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
}
