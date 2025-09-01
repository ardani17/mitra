<?php

namespace App\Http\Controllers;

use App\Models\BotUser;
use App\Models\BotRole;
use App\Models\BotRegistrationRequest;
use App\Models\BotUserActivityLog;
use App\Services\Telegram\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TelegramBotUserController extends Controller
{
    protected $telegramService;
    
    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }
    
    /**
     * Display list of bot users
     */
    public function index(Request $request)
    {
        $query = BotUser::with('role');
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by role
        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('telegram_id', 'like', "%{$search}%");
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $roles = BotRole::all();
        
        return view('telegram-bot.users.index', compact('users', 'roles'));
    }
    
    /**
     * Display pending registration requests
     */
    public function registrations(Request $request)
    {
        $query = BotRegistrationRequest::where('status', 'pending');
        
        $requests = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('telegram-bot.registrations.index', compact('requests'));
    }
    
    /**
     * Show user details
     */
    public function show($id)
    {
        $user = BotUser::with(['role', 'activityLogs' => function($query) {
            $query->latest()->limit(50);
        }])->findOrFail($id);
        
        // Get user statistics
        $stats = [
            'total_activities' => $user->activityLogs()->count(),
            'activities_today' => $user->activityLogs()->whereDate('created_at', today())->count(),
            'activities_this_week' => $user->activityLogs()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'activities_this_month' => $user->activityLogs()->whereMonth('created_at', now()->month)->count(),
        ];
        
        return view('telegram-bot.users.show', compact('user', 'stats'));
    }
    
    /**
     * Update user role
     */
    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role_id' => 'required|exists:bot_roles,id'
        ]);
        
        $user = BotUser::findOrFail($id);
        $oldRole = $user->role;
        
        $user->role_id = $request->role_id;
        $user->save();
        
        // Log the change
        BotUserActivityLog::logActivity(
            $user->id,
            'role_changed',
            "Role changed from {$oldRole->name} to {$user->role->name}",
            [
                'old_role_id' => $oldRole->id,
                'new_role_id' => $request->role_id,
                'changed_by' => auth()->user()->name
            ]
        );
        
        // Notify user via Telegram
        try {
            $this->telegramService->sendMessage(
                $user->telegram_id,
                "🎭 <b>Role Updated</b>\n\n" .
                "Your role has been changed to: <b>{$user->role->display_name}</b>\n" .
                "You now have access to new features based on your role."
            );
        } catch (\Exception $e) {
            Log::error('Failed to send role update notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return redirect()->route('telegram-bot.users.show', $id)
            ->with('success', 'User role updated successfully');
    }
    
    /**
     * Approve registration request
     */
    public function approve($id)
    {
        DB::beginTransaction();
        
        try {
            $request = BotRegistrationRequest::findOrFail($id);
            
            if ($request->status !== 'pending') {
                return redirect()->back()
                    ->with('error', 'This request has already been processed');
            }
            
            // Create or update bot user
            $botUser = BotUser::updateOrCreate(
                ['telegram_id' => $request->telegram_id],
                [
                    'username' => $request->username,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'role_id' => 4, // Default to 'user' role
                    'status' => 'active',
                    'approved_at' => now(),
                    'approved_by' => auth()->id()
                ]
            );
            
            // Update request status
            $request->status = 'approved';
            $request->processed_at = now();
            $request->processed_by = auth()->id();
            $request->save();
            
            // Log activity
            BotUserActivityLog::logActivity(
                $botUser->id,
                'registration_approved',
                'Registration approved by admin',
                [
                    'approved_by' => auth()->user()->name,
                    'request_id' => $request->id
                ]
            );
            
            // Send notification to user
            try {
                $this->telegramService->sendMessage(
                    $request->telegram_id,
                    "✅ <b>Registration Approved!</b>\n\n" .
                    "Welcome! Your registration has been approved.\n" .
                    "You now have access to all bot features.\n\n" .
                    "Type /help to see available commands."
                );
            } catch (\Exception $e) {
                Log::error('Failed to send approval notification', [
                    'request_id' => $request->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('telegram-bot.registrations')
                ->with('success', 'Registration approved successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve registration', [
                'request_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to approve registration: ' . $e->getMessage());
        }
    }
    
    /**
     * Reject registration request
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);
        
        DB::beginTransaction();
        
        try {
            $registrationRequest = BotRegistrationRequest::findOrFail($id);
            
            if ($registrationRequest->status !== 'pending') {
                return redirect()->back()
                    ->with('error', 'This request has already been processed');
            }
            
            // Update request status
            $registrationRequest->status = 'rejected';
            $registrationRequest->processed_at = now();
            $registrationRequest->processed_by = auth()->id();
            $registrationRequest->notes = $request->reason;
            $registrationRequest->save();
            
            // Send notification to user
            try {
                $message = "❌ <b>Registration Rejected</b>\n\n";
                if ($request->reason) {
                    $message .= "Reason: {$request->reason}\n\n";
                }
                $message .= "Please contact the administrator for more information.";
                
                $this->telegramService->sendMessage(
                    $registrationRequest->telegram_id,
                    $message
                );
            } catch (\Exception $e) {
                Log::error('Failed to send rejection notification', [
                    'request_id' => $registrationRequest->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('telegram-bot.registrations')
                ->with('success', 'Registration rejected');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject registration', [
                'request_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to reject registration: ' . $e->getMessage());
        }
    }
    
    /**
     * Ban user
     */
    public function ban(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);
        
        $user = BotUser::findOrFail($id);
        
        if ($user->status === 'banned') {
            return redirect()->back()
                ->with('error', 'User is already banned');
        }
        
        $user->ban($request->reason);
        
        // Log activity
        BotUserActivityLog::logActivity(
            $user->id,
            'user_banned',
            "User banned: {$request->reason}",
            [
                'banned_by' => auth()->user()->name,
                'reason' => $request->reason
            ]
        );
        
        // Send notification to user
        try {
            $this->telegramService->sendMessage(
                $user->telegram_id,
                "🚫 <b>Account Banned</b>\n\n" .
                "Your account has been banned.\n" .
                "Reason: {$request->reason}\n\n" .
                "Please contact the administrator if you believe this is a mistake."
            );
        } catch (\Exception $e) {
            Log::error('Failed to send ban notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return redirect()->route('telegram-bot.users.show', $id)
            ->with('success', 'User banned successfully');
    }
    
    /**
     * Unban user
     */
    public function unban($id)
    {
        $user = BotUser::findOrFail($id);
        
        if ($user->status !== 'banned') {
            return redirect()->back()
                ->with('error', 'User is not banned');
        }
        
        $user->activate();
        
        // Log activity
        BotUserActivityLog::logActivity(
            $user->id,
            'user_unbanned',
            'User unbanned',
            [
                'unbanned_by' => auth()->user()->name
            ]
        );
        
        // Send notification to user
        try {
            $this->telegramService->sendMessage(
                $user->telegram_id,
                "✅ <b>Account Reactivated</b>\n\n" .
                "Your account has been reactivated.\n" .
                "You can now use the bot again.\n\n" .
                "Type /help to see available commands."
            );
        } catch (\Exception $e) {
            Log::error('Failed to send unban notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return redirect()->route('telegram-bot.users.show', $id)
            ->with('success', 'User unbanned successfully');
    }
    
    /**
     * Suspend user
     */
    public function suspend(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'until' => 'nullable|date|after:now'
        ]);
        
        $user = BotUser::findOrFail($id);
        
        if ($user->status === 'suspended') {
            return redirect()->back()
                ->with('error', 'User is already suspended');
        }
        
        $user->suspend($request->reason, $request->until);
        
        // Log activity
        BotUserActivityLog::logActivity(
            $user->id,
            'user_suspended',
            "User suspended: {$request->reason}",
            [
                'suspended_by' => auth()->user()->name,
                'reason' => $request->reason,
                'until' => $request->until
            ]
        );
        
        // Send notification to user
        try {
            $message = "⚠️ <b>Account Suspended</b>\n\n" .
                "Your account has been suspended.\n" .
                "Reason: {$request->reason}\n";
            
            if ($request->until) {
                $message .= "Until: " . \Carbon\Carbon::parse($request->until)->format('d M Y H:i') . "\n";
            }
            
            $message .= "\nPlease contact the administrator for more information.";
            
            $this->telegramService->sendMessage(
                $user->telegram_id,
                $message
            );
        } catch (\Exception $e) {
            Log::error('Failed to send suspension notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return redirect()->route('telegram-bot.users.show', $id)
            ->with('success', 'User suspended successfully');
    }
    
    /**
     * Export users to CSV
     */
    public function export(Request $request)
    {
        $query = BotUser::with('role');
        
        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }
        
        $users = $query->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="telegram_bot_users_' . date('Y-m-d_His') . '.csv"',
        ];
        
        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Telegram ID',
                'Username',
                'First Name',
                'Last Name',
                'Role',
                'Status',
                'Registered At',
                'Approved At',
                'Last Activity'
            ]);
            
            // Data rows
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->telegram_id,
                    $user->username,
                    $user->first_name,
                    $user->last_name,
                    $user->role->display_name ?? 'N/A',
                    $user->status,
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->approved_at ? $user->approved_at->format('Y-m-d H:i:s') : 'N/A',
                    $user->last_activity_at ? $user->last_activity_at->format('Y-m-d H:i:s') : 'Never'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}