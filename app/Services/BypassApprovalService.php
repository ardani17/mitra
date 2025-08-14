<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class BypassApprovalService
{
    /**
     * Check if director bypass approval is enabled
     */
    public static function isEnabled(): bool
    {
        return Setting::isDirectorBypassEnabled();
    }

    /**
     * Check if user can bypass approval workflow
     */
    public static function canBypass(User $user): bool
    {
        return $user->hasRole('direktur') && static::isEnabled();
    }

    /**
     * Check if current authenticated user can bypass approval
     */
    public static function canCurrentUserBypass(): bool
    {
        $user = auth()->user();
        return $user ? static::canBypass($user) : false;
    }

    /**
     * Log bypass action for audit trail
     */
    public static function logBypassAction(string $action, array $data = []): void
    {
        Log::info("Director bypass action: {$action}", array_merge([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'timestamp' => now(),
            'bypass_enabled' => static::isEnabled()
        ], $data));
    }

    /**
     * Get bypass status information
     */
    public static function getBypassInfo(): array
    {
        $user = auth()->user();
        $isEnabled = static::isEnabled();
        $canBypass = $user ? static::canBypass($user) : false;

        return [
            'bypass_enabled' => $isEnabled,
            'user_is_director' => $user ? $user->hasRole('direktur') : false,
            'can_bypass' => $canBypass,
            'status_message' => static::getStatusMessage($isEnabled, $canBypass)
        ];
    }

    /**
     * Get human-readable status message
     */
    private static function getStatusMessage(bool $isEnabled, bool $canBypass): string
    {
        if (!$isEnabled) {
            return 'Fitur bypass approval tidak aktif';
        }

        if ($canBypass) {
            return 'Anda dapat membuat pengeluaran tanpa approval';
        }

        return 'Fitur bypass hanya tersedia untuk direktur';
    }

    /**
     * Check if expense should bypass approval workflow
     */
    public static function shouldBypassExpenseApproval(User $creator): bool
    {
        $canBypass = static::canBypass($creator);
        
        if ($canBypass) {
            static::logBypassAction('expense_approval_bypassed', [
                'creator_id' => $creator->id,
                'creator_name' => $creator->name
            ]);
        }

        return $canBypass;
    }

    /**
     * Get settings related to bypass approval
     */
    public static function getRelatedSettings(): array
    {
        return [
            'director_bypass_enabled' => Setting::isDirectorBypassEnabled(),
            'high_amount_threshold' => Setting::get('expense_high_amount_threshold', 10000000),
            'notification_enabled' => Setting::get('expense_approval_notification_enabled', true)
        ];
    }

    /**
     * Validate bypass conditions
     */
    public static function validateBypassConditions(User $user): array
    {
        $errors = [];
        $warnings = [];

        if (!static::isEnabled()) {
            $errors[] = 'Fitur bypass approval tidak aktif';
        }

        if (!$user->hasRole('direktur')) {
            $errors[] = 'Hanya direktur yang dapat menggunakan bypass approval';
        }

        if (static::isEnabled() && $user->hasRole('direktur')) {
            $warnings[] = 'Pengeluaran akan langsung disetujui tanpa workflow approval';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
}