<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
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
            switch ($user->roleID) {
                case 1:
                    return redirect('/admin/dashboard');
                case 2:
                    return redirect('/manager/dashboard');
                case 3:
                    return redirect('/farmer/dashboard');
                default:
                    return redirect('/');
            }
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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
