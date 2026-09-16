<?php

namespace App\Providers;

use App\Models\Notification;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // This app has no "dashboard"/"home" route, so the framework's default
        // guest-redirect (used when an already-logged-in user hits /login)
        // would otherwise silently fall back to "/" instead of the user's
        // actual role dashboard. This is also the single-session-per-browser
        // enforcement point: the `guest` middleware already runs before the
        // login controller for both GET and POST /login, so a second account
        // can never be authenticated while one is already logged in on this
        // browser — the attempt is redirected away before it's processed,
        // with a flashed message explaining why.
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            $user = $request->user();

            if ($user && $request->routeIs('login')) {
                session()->flash('login_blocked', "An account is already logged in as \"{$user->name}\". Please logout first before logging in to another account.");
            }

            return $user?->dashboardUrl() ?? '/';
        });

        // Feeds the notification bell shown on every role's topbar.
        View::composer(['manager.layout', 'farmer.layout', 'admin.layout'], function ($view) {
            $user = Auth::user();

            if (!$user) {
                $view->with(['announcementNotifications' => collect(), 'unreadAnnouncementCount' => 0]);
                return;
            }

            $notifications = Notification::where('user_id', $user->id)
                ->with('announcement.creator', 'announcement.recipients', 'loan.loanRequest.farmer')
                ->orderBy('created_at', 'desc')
                ->take(8)
                ->get();

            $unreadCount = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            $view->with([
                'announcementNotifications' => $notifications,
                'unreadAnnouncementCount' => $unreadCount,
            ]);
        });
    }
}
