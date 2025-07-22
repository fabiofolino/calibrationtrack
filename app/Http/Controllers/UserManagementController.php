<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Display user management page (admin only)
     */
    public function index()
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can manage users.');
        }

        $users = auth()->user()->company->users()
            ->select('id', 'name', 'email', 'role', 'email_verified_at', 'created_at', 'updated_at')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/UserManagement', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly invited user
     */
    public function store(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can invite users.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->where(function ($query) use ($request) {
                    return $query->where('company_id', auth()->user()->company_id);
                }),
            ],
            'role' => 'required|in:admin,member',
        ]);

        // Generate a temporary password
        $temporaryPassword = Str::random(12);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($temporaryPassword),
            'company_id' => auth()->user()->company_id,
            'role' => $request->role,
        ]);

        // Send invitation email (optional implementation)
        // Mail::to($user)->send(new UserInvitation($user, $temporaryPassword));

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} has been invited successfully. Temporary password: {$temporaryPassword}");
    }

    /**
     * Update user role
     */
    public function update(Request $request, User $user)
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can update users.');
        }

        // Verify user belongs to same company
        if ($user->company_id !== auth()->user()->company_id) {
            abort(404, 'User not found.');
        }

        // Prevent admin from demoting themselves if they're the only admin
        if ($user->id === auth()->id() && $request->role !== 'admin') {
            $adminCount = auth()->user()->company->users()->where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return redirect()->back()
                    ->withErrors(['role' => 'Cannot demote the last administrator.']);
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,member',
            'is_suspended' => 'boolean',
        ]);

        $user->update([
            'name' => $request->name,
            'role' => $request->role,
        ]);

        // Handle suspension (we'll add a is_suspended column)
        if ($request->has('is_suspended')) {
            $user->update(['is_suspended' => $request->is_suspended]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} has been updated successfully.");
    }

    /**
     * Remove user from company
     */
    public function destroy(User $user)
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can remove users.');
        }

        // Verify user belongs to same company
        if ($user->company_id !== auth()->user()->company_id) {
            abort(404, 'User not found.');
        }

        // Prevent admin from removing themselves if they're the only admin
        if ($user->id === auth()->id()) {
            $adminCount = auth()->user()->company->users()->where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return redirect()->back()
                    ->withErrors(['error' => 'Cannot remove the last administrator.']);
            }
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$userName} has been removed successfully.");
    }

    /**
     * Suspend user
     */
    public function suspend(User $user)
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can suspend users.');
        }

        // Verify user belongs to same company
        if ($user->company_id !== auth()->user()->company_id) {
            abort(404, 'User not found.');
        }

        // Prevent admin from suspending themselves if they're the only admin
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->withErrors(['error' => 'Cannot suspend yourself.']);
        }

        $user->update(['is_suspended' => true]);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} has been suspended.");
    }

    /**
     * Reactivate user
     */
    public function reactivate(User $user)
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can reactivate users.');
        }

        // Verify user belongs to same company
        if ($user->company_id !== auth()->user()->company_id) {
            abort(404, 'User not found.');
        }

        $user->update(['is_suspended' => false]);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} has been reactivated.");
    }
}