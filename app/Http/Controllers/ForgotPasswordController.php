<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function showForm()
    {
        return view('forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! in_array($user->RoleID, [1, 2], true)) {
            return back()->with('error', 'Reset links are available only for Manager and Admin accounts.');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($user->email));

        Mail::raw("Use the link below to reset your password:\n\n{$resetUrl}\n\nThis link expires in 60 minutes.", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Password Reset Request');
        });

        return back()->with('status', 'A password reset link has been sent to your email address.');
    }

    public function showResetForm($token)
    {
        return view('reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request, $token)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->latest('created_at')
            ->first();

        if (! $record || ! Hash::check($token, $record->token) || now()->diffInMinutes($record->created_at) > 60) {
            return back()->with('error', 'Invalid or expired reset link.');
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! in_array($user->RoleID, [1, 2], true)) {
            return back()->with('error', 'Reset links are available only for Manager and Admin accounts.');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return redirect('/')->with('status', 'Your password has been reset successfully. You can now log in.');
    }
}
