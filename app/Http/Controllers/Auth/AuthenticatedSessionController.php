<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Failed attempts a farmer account is allowed before it locks. Only a
     * manager can unlock it afterward, from User Management — a wrong
     * password can never clear the lock, even once corrected.
     */
    private const MAX_FARMER_LOGIN_ATTEMPTS = 3;

    /**
     * Display the login view.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // Try to login with username first, then with email
        $loginField = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($loginField, $credentials['username'])->first();

        if ($user && (int) $user->roleID === 3 && $user->status === 'locked') {
            return back()->withErrors([
                'username' => 'This account has been locked after too many failed login attempts. Please contact your cooperative manager to have it unlocked.',
            ])->onlyInput('username');
        }

        // "inactive" also covers a brand-new account that has never logged in yet
        // (which must still be allowed through, so it can activate itself). Only
        // block accounts that were previously used and have since been manually
        // deactivated. Admin accounts can't be deactivated at all, so this only
        // ever applies to Manager/Farmer accounts.
        if ($user && (int) $user->roleID !== 1 && $user->status === 'inactive' && ! $user->firstTimelogin) {
            $contact = (int) $user->roleID === 3 ? 'your cooperative manager' : 'an administrator';

            return back()->withErrors([
                'username' => "This account has been deactivated. Please contact {$contact}.",
            ])->onlyInput('username');
        }

        if (Auth::attempt([$loginField => $credentials['username'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();

            // The system marks an account active the moment it's actually used to
            // log in, rather than having that chosen manually at creation. Accounts
            // an admin/manager has locked or archived stay that way even after a
            // successful login.
            $user = Auth::user();
            if (! in_array($user->status, ['locked', 'archived'], true)) {
                $user->update([
                    'status' => 'active',
                    'isloggedin' => true,
                    'firstTimelogin' => false,
                    'FailedLoginAttemps' => 0,
                ]);
            }

            // Redirect based on user role
            return redirect($user->dashboardUrl());
        }

        if ($user && (int) $user->roleID === 3 && $user->status !== 'archived') {
            $attempts = $user->FailedLoginAttemps + 1;

            if ($attempts >= self::MAX_FARMER_LOGIN_ATTEMPTS) {
                $user->update([
                    'FailedLoginAttemps' => $attempts,
                    'status' => 'locked',
                ]);

                return back()->withErrors([
                    'username' => 'This account has been locked after too many failed login attempts. Please contact your cooperative manager to have it unlocked.',
                ])->onlyInput('username');
            }

            $user->update(['FailedLoginAttemps' => $attempts]);

            $remaining = self::MAX_FARMER_LOGIN_ATTEMPTS - $attempts;

            return back()->withErrors([
                'username' => "The provided credentials do not match our records. {$remaining} attempt(s) remaining before this account is locked.",
            ])->onlyInput('username');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->update(['isloggedin' => false]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
