<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * A session cookie can outlive the account's authorization to use it —
     * e.g. an admin locks/archives a user mid-session. The login check alone
     * won't catch that, so re-check status on every authenticated request
     * and kill the session the moment it goes stale.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && in_array($user->status, ['locked', 'archived'], true)) {
            $user->update(['isloggedin' => false]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'username' => 'Your account has been deactivated. Please contact an administrator.',
            ]);
        }

        return $next($request);
    }
}
