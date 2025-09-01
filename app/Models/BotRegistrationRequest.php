<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotRegistrationRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'telegram_id',
        'username',
        'first_name',
        'last_name',
        'reason',
        'additional_info',
        'status',
        'reviewed_by',
        'review_note',
        'requested_at',
        'reviewed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'additional_info' => 'array',
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the reviewer (admin who reviewed the request).
     */
    public function reviewer()
    {
        return $this->belongsTo(BotUser::class, 'reviewed_by');
    }

    /**
     * Get the associated bot user.
     */
    public function botUser()
    {
        return $this->belongsTo(BotUser::class, 'telegram_id', 'telegram_id');
    }

    /**
     * Check if the request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the registration request.
     */
    public function approve($reviewerId, ?string $note = null): void
    {
        $this->status = 'approved';
        $this->reviewed_by = $reviewerId;
        $this->review_note = $note;
        $this->reviewed_at = now();
        $this->save();

        // Also approve the associated bot user if exists
        if ($this->botUser) {
            $this->botUser->approve($reviewerId);
        }
    }

    /**
     * Reject the registration request.
     */
    public function reject($reviewerId, ?string $reason = null): void
    {
        $this->status = 'rejected';
        $this->reviewed_by = $reviewerId;
        $this->review_note = $reason;
        $this->reviewed_at = now();
        $this->save();

        // Update the associated bot user status if exists
        if ($this->botUser) {
            $metadata = $this->botUser->metadata ?? [];
            $metadata['rejection_reason'] = $reason;
            $metadata['rejected_at'] = now()->toIso8601String();
            $this->botUser->metadata = $metadata;
            $this->botUser->save();
        }
    }

    /**
     * Get the display name for the requester.
     */
    public function getDisplayName(): string
    {
        if ($this->first_name || $this->last_name) {
            return trim($this->first_name . ' ' . $this->last_name);
        }
        
        if ($this->username) {
            return '@' . $this->username;
        }
        
        return 'User #' . $this->telegram_id;
    }

    /**
     * Scope for pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for recent requests.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('requested_at', '>=', now()->subDays($days));
    }

    /**
     * Create a registration request from Telegram data.
     */
    public static function createFromTelegram(array $telegramUser, ?string $reason = null): self
    {
        return static::create([
            'telegram_id' => $telegramUser['id'],
            'username' => $telegramUser['username'] ?? null,
            'first_name' => $telegramUser['first_name'] ?? null,
            'last_name' => $telegramUser['last_name'] ?? null,
            'reason' => $reason,
            'status' => 'pending',
            'requested_at' => now(),
        ]);
    }

    /**
     * Check if a user has a pending request.
     */
    public static function hasPendingRequest($telegramId): bool
    {
        return static::where('telegram_id', $telegramId)
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Get the latest request for a user.
     */
    public static function getLatestForUser($telegramId): ?self
    {
        return static::where('telegram_id', $telegramId)
            ->latest('requested_at')
            ->first();
    }
}