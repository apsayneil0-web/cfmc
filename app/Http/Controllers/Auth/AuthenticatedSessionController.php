<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        if ($user && $this->hasActiveSessionElsewhere($user, $request)) {
            return back()->withErrors([
                'username' => 'This account is already logged in on another device or browser. Please logout there first before logging in again.',
            ])->onlyInput('username');
        }

        if (Auth::attempt([$loginField => $credentials['username'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $user = Auth::user();

            // A farmer's very first login is gated behind an SMS OTP sent to the
            // phone number on file, rather than dropping straight into a session.
            // Password is already confirmed correct at this point; log the guard
            // back out until the code is verified.
            if ((int) $user->roleID === 3 && $user->firstTimelogin) {
                Auth::logout();

                $otp = (string) random_int(100000, 999999);
                $user->update([
                    'OTP' => $otp,
                    'OTPexpriry' => now()->addMinutes(10),
                ]);

                app(SmsService::class)->send(
                    $user->Phonenumber,
                    "Your CFMC login verification code is {$otp}. It expires in 10 minutes."
                );

                $request->session()->put('farmer_otp_user_id', $user->id);

                return redirect()->route('farmer.otp.verify.form');
            }

            $request->session()->regenerate();

            // The system marks an account active the moment it's actually used to
            // log in, rather than having that chosen manually at creation. Accounts
            // an admin/manager has locked or archived stay that way even after a
            // successful login.
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
     * Whether this user already has a live session on a different
     * browser/device — i.e. a row in the `sessions` table for them, other
     * than the current (still-unauthenticated) one, that hasn't gone idle
     * past the configured session lifetime yet. Using the sessions table
     * itself (rather than a DB flag on the user) means a session that was
     * never explicitly logged out still self-expires normally instead of
     * permanently locking the account out of logging in anywhere.
     */
    private function hasActiveSessionElsewhere(User $user, Request $request): bool
    {
        if (config('session.driver') !== 'database') {
            return false;
        }

        $cutoff = now()->subMinutes((int) config('session.lifetime'))->timestamp;

        return DB::table(config('session.table', 'sessions'))
            ->where('user_id', $user->id)
            ->where('id', '!=', $request->session()->getId())
            ->where('last_activity', '>=', $cutoff)
            ->exists();
    }

    /**
     * Destroy an authenticated session. The client-side inactivity timer
     * (resources/js/app.js) submits this same logout form with
     * reason=timeout for an immediate redirect while a tab sits idle; the
     * actual, unbypassable enforcement is the EnsureSessionIsActive
     * middleware, which performs the same logout server-side on the next
     * request regardless of whether this was ever called from JS.
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

        if ($request->input('reason') === 'timeout') {
            return redirect()->route('login')->withErrors([
                'username' => 'Your session has expired due to inactivity. Please log in again.',
            ]);
        }

        return redirect()->route('login');
    }
}
