<?php

namespace App\Http\Middleware;

use App\Support\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionIsActive
{
    /**
     * Session key used to track the last request timestamp for the
     * inactivity timeout. Deliberately separate from Laravel's own
     * session "lifetime" — this is a shorter, purpose-specific clock with
     * its own message, not a replacement for SESSION_LIFETIME.
     */
    private const LAST_ACTIVITY_KEY = 'inactivity_last_activity_at';

    /**
     * This is the real enforcement point (see config/session.php ->
     * "inactivity_timeout" for where the 15 minutes is configured). It
     * runs on every authenticated request, so a stale session gets logged
     * out here even if the browser's JavaScript timer was disabled,
     * blocked, or never loaded — the client-side timer in resources/js/app.js
     * is only there for an immediate redirect while the tab sits idle;
     * this middleware is what actually can't be bypassed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $timeoutMinutes = (int) config('session.inactivity_timeout');
            $cutoff = time() - ($timeoutMinutes * 60);
            $lastActivity = $request->session()->get(self::LAST_ACTIVITY_KEY);

            if ($lastActivity !== null && $lastActivity < $cutoff) {
                $user = Auth::user();
                if ($user) {
                    $user->update(['isloggedin' => false]);
                    ActivityLogger::log($user, 'auth.session_timeout', "{$user->name}'s session expired due to inactivity.", $user);
                }

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'username' => 'Your session has expired due to inactivity. Please log in again.',
                ]);
            }

            $request->session()->put(self::LAST_ACTIVITY_KEY, time());
        }

        return $next($request);
    }
}
