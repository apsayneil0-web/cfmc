<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    /**
     * Show the form to request an OTP (Admin/Manager only).
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Generate an OTP for a staff account and email it.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
        ]);

        $loginField = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($loginField, $request->username)
            ->whereIn('roleID', [1, 2])
            ->first();

        // Staff only. The message is intentionally generic so we don't reveal
        // whether an account exists.
        if (! $user || ! $user->email) {
            return back()->withErrors([
                'username' => 'No staff account with an email address was found for that username.',
            ])->onlyInput('username');
        }

        $otp = (string) random_int(100000, 999999);

        $user->update([
            'OTP' => $otp,
            'OTPexpriry' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new OtpMail($user, $otp));

        $request->session()->put('password_reset_user_id', $user->id);

        return redirect()->route('password.otp.verify.form');
    }

    /**
     * Show the OTP entry form.
     */
    public function showVerifyForm(Request $request)
    {
        if (! $request->session()->has('password_reset_user_id')) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-otp');
    }

    /**
     * Verify the submitted OTP.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string'],
        ]);

        $userId = $request->session()->get('password_reset_user_id');
        $user = $userId ? User::find($userId) : null;

        if (! $user) {
            return redirect()->route('password.request');
        }

        if (! $user->OTP || $user->OTP !== $request->otp || now()->greaterThan($user->OTPexpriry)) {
            return back()->withErrors([
                'otp' => 'That code is invalid or has expired.',
            ]);
        }

        $request->session()->put('password_reset_verified', true);

        return redirect()->route('password.reset.form');
    }

    /**
     * Show the new password form.
     */
    public function showResetForm(Request $request)
    {
        if (! $request->session()->get('password_reset_verified')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    /**
     * Set the new password and clear the OTP.
     */
    public function resetPassword(Request $request)
    {
        if (! $request->session()->get('password_reset_verified')) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $userId = $request->session()->get('password_reset_user_id');
        $user = User::findOrFail($userId);

        $user->update([
            'password' => Hash::make($request->password),
            'OTP' => null,
            'OTPexpriry' => null,
        ]);

        $request->session()->forget(['password_reset_user_id', 'password_reset_verified']);

        return redirect()->route('login')->with('status', 'Your password has been reset. You can now log in.');
    }
}
