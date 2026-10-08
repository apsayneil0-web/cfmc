<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Show the logged-in user's settings page. Deliberately minimal for
     * now — theme is the one per-user preference this app actually has
     * (the existing topbar dark/light toggle, backed by localStorage).
     * A natural place to add more account-level preferences later.
     */
    public function edit()
    {
        return view('settings.edit', ['user' => Auth::user()]);
    }
}
