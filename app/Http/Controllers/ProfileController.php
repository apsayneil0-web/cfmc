<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Roles whose profile picture lives on the Staff record. Everyone else
     * (Farmer) stores it on their linked Farmer membership record.
     */
    private const STAFF_ROLES = [1, 2];

    /**
     * Show the logged-in user's own account page.
     */
    public function edit()
    {
        $user = Auth::user();

        return view('profile.edit', ['user' => $user]);
    }

    /**
     * Update the logged-in user's own phone number. Name is intentionally
     * not editable here — for Admin/Manager accounts it's recomputed from
     * the Staff record's first/middle/last name every time an admin edits
     * the user elsewhere, so a direct self-edit here would just get
     * silently overwritten on the next admin touch. Email and username are
     * also left alone, since both are load-bearing for login/lookup
     * elsewhere (forgot-password, farmer OTP).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'Phonenumber' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

        ActivityLogger::log($user, 'user.updated', "{$user->name} updated their own phone number.", $user);

        return back()->with('status', 'Your profile has been updated.');
    }

    /**
     * Change the logged-in user's own password. Requires the current
     * password, same as changing it anywhere else in the app requires an
     * admin/manager — a hijacked session shouldn't be able to lock the real
     * owner out without knowing the existing password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Your current password is incorrect.',
            ])->onlyInput('current_password');
        }

        $user->update([
            'password' => Hash::make($validated['password']),
            // A plaintext temp password (set by whoever provisioned this
            // account) is now stale and would be actively misleading to
            // leave in User Management once the owner has set their own.
            'temp_password' => null,
        ]);

        ActivityLogger::log($user, 'user.password_reset', "{$user->name} changed their own password.", $user);

        return back()->with('status', 'Your password has been changed.');
    }

    /**
     * Update the logged-in user's own profile picture, regardless of role.
     */
    public function updatePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|max:2048',
        ]);

        $user = Auth::user();
        $path = $request->file('profile_picture')->storeAs(
            'profile_pictures',
            time().'_'.$request->file('profile_picture')->getClientOriginalName(),
            'public'
        );

        if (in_array((int) $user->roleID, self::STAFF_ROLES, true)) {
            if ($user->staff?->profile_picture) {
                Storage::disk('public')->delete($user->staff->profile_picture);
            }

            Staff::updateOrCreate(['user_id' => $user->id], ['profile_picture' => $path]);
        } else {
            $farmer = $user->farmer;

            if (! $farmer) {
                Storage::disk('public')->delete($path);

                return response()->json([
                    'success' => false,
                    'message' => 'No membership record is linked to this account yet.',
                ], 422);
            }

            if ($farmer->profile_picture) {
                Storage::disk('public')->delete($farmer->profile_picture);
            }

            $farmer->update(['profile_picture' => $path]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated!',
            'url' => asset('storage/'.$path),
        ]);
    }
}
