<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = trim($request->username);
        $loginLower = Str::lower($login);

        $user = User::whereRaw('LOWER(username) = ?', [$loginLower])
            ->orWhereRaw('LOWER(email) = ?', [$loginLower])
            ->first();

        if (! $user) {
            return back()->with('error', 'Username not found');
        }

        if (! Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Incorrect password');
        }

        Auth::login($user);

        $user->isLoggedIn = 1;
        $user->save();

        // Redirect based on role - convert RoleID to integer for proper comparison
        $roleId = (int) $user->RoleID;

        if ($roleId === 1) {
            return redirect('/dashboard/admin');
        } elseif ($roleId === 2) {
            return redirect('/dashboard/manager');
        } else {
            return redirect('/dashboard/farmer');
        }
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->isLoggedIn = 0;
            $user->save();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('message', 'You have been logged out successfully');
    }
}
