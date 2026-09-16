<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Being logged in was never the same as being authorized for a given
     * role's routes — the `auth` middleware only checked the former. This
     * checks the account's roleID against the role(s) allowed for the
     * route, e.g. `role:1` for admin-only, `role:1,2` for admin or manager.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();
        $allowed = array_map('intval', $roles);

        if ($user && in_array((int) $user->roleID, $allowed, true)) {
            return $next($request);
        }

        if ($user) {
            session()->flash('role_denied', "You don't have permission to access that page.");

            return redirect($user->dashboardUrl());
        }

        return redirect()->route('login');
    }
}
