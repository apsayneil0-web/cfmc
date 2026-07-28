<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MembershipApprovalController extends Controller
{
    /**
     * Display pending membership applications awaiting approval.
     */
    public function index()
    {
        $applications = Farmer::with('crop')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.membership-approval', compact('applications'));
    }

    /**
     * Approve a membership application. This also provisions the farmer's
     * login account, unless one is already linked (e.g. re-approval after
     * an unarchive).
     */
    public function approve(Farmer $farmer)
    {
        $credentials = DB::transaction(function () use ($farmer) {
            $farmer->update([
                'status' => 'approved',
                'rejection_reason' => null,
            ]);

            if ($farmer->account_user_id) {
                return null;
            }

            $username = $this->generateUsername($farmer);
            $password = Str::password(10);

            $user = User::create([
                'name' => $farmer->full_name,
                'username' => $username,
                'email' => $username.'@farm.local',
                'password' => Hash::make($password),
                'temp_password' => $password,
                'status' => 'inactive',
                'roleID' => 3,
                'Phonenumber' => $farmer->contact_number,
                'firstTimelogin' => true,
                'isloggedin' => false,
                'FailedLoginAttemps' => 0,
            ]);

            $farmer->update(['account_user_id' => $user->id]);

            return ['username' => $username, 'password' => $password];
        });

        $message = "{$farmer->full_name}'s membership application has been approved.";

        if ($credentials) {
            $message .= " Login account created — username: {$credentials['username']}, temporary password: {$credentials['password']}. Please share these with the farmer directly.";
        }

        return redirect()->route('admin.membership-approval')
            ->with('success', $message);
    }

    /**
     * Build a unique username from the farmer's name (e.g. "juan.delacruz",
     * "juan.delacruz2" on collision).
     */
    private function generateUsername(Farmer $farmer): string
    {
        $base = Str::slug($farmer->first_name.' '.$farmer->last_name, '.');
        $username = $base;
        $suffix = 1;

        while (User::where('username', $username)->exists()) {
            $suffix++;
            $username = $base.$suffix;
        }

        return $username;
    }

    /**
     * Reject a membership application.
     */
    public function reject(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $farmer->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return redirect()->route('admin.membership-approval')
            ->with('success', "{$farmer->full_name}'s membership application has been rejected.");
    }
}
