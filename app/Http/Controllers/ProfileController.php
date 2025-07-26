<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Role;
use App\Models\Company;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $data = [
            'user' => $request->user(),
            'activeCompany' => Company::getActive(),
        ];

        // Add user management data for direktur
        if ($request->user()->hasRole('direktur')) {
            $data['userStats'] = [
                'total' => User::count(),
                'direktur' => User::whereHas('roles', function($q) { $q->where('name', 'direktur'); })->count(),
                'project_manager' => User::whereHas('roles', function($q) { $q->where('name', 'project_manager'); })->count(),
                'finance_manager' => User::whereHas('roles', function($q) { $q->where('name', 'finance_manager'); })->count(),
                'staf' => User::whereHas('roles', function($q) { $q->where('name', 'staf'); })->count(),
            ];
            
            $data['recentUsers'] = User::with('roles')->latest()->take(5)->get();
        }

        return view('profile.edit', $data);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update company information (Only for Direktur)
     */
    public function updateCompany(Request $request): RedirectResponse
    {
        // Check if user is direktur
        if (!$request->user()->hasRole('direktur')) {
            abort(403, 'Hanya direktur yang dapat mengatur informasi perusahaan.');
        }

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:20'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'contact_person' => ['nullable', 'string', 'max:255'],
        ]);

        // Get or create active company
        $activeCompany = Company::getActive();
        
        if ($activeCompany) {
            // Update existing active company
            $activeCompany->update([
                'name' => $validated['company_name'],
                'email' => $validated['company_email'],
                'phone' => $validated['company_phone'],
                'address' => $validated['company_address'],
                'contact_person' => $validated['contact_person'],
            ]);
        } else {
            // Create new company and set as active
            $company = Company::create([
                'name' => $validated['company_name'],
                'email' => $validated['company_email'],
                'phone' => $validated['company_phone'],
                'address' => $validated['company_address'],
                'contact_person' => $validated['contact_person'],
                'is_active' => true,
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'company-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
