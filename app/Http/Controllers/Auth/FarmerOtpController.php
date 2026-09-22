<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FarmerOtpController extends Controller
{
    /**
     * Show the OTP entry form a farmer lands on after a correct password on
     * their first login.
     */
    public function showVerifyForm(Request $request)
    {
        if (! $request->session()->has('farmer_otp_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.verify-farmer-otp');
    }

    /**
     * Verify the submitted OTP and complete the login it was gating.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string'],
        ]);

        $userId = $request->session()->get('farmer_otp_user_id');
        $user = $userId ? User::find($userId) : null;

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->OTP || $user->OTP !== $request->otp || now()->greaterThan($user->OTPexpriry)) {
            return back()->withErrors([
                'otp' => 'That code is invalid or has expired.',
            ]);
        }

        $user->update([
            'OTP' => null,
            'OTPexpriry' => null,
            'status' => 'active',
            'isloggedin' => true,
            'firstTimelogin' => false,
            'FailedLoginAttemps' => 0,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('farmer_otp_user_id');

        return redirect($user->dashboardUrl());
    }
}
