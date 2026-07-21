<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Roles whose profile picture lives on the Staff record. Everyone else
     * (Farmer) stores it on their linked Farmer membership record.
     */
    private const STAFF_ROLES = [1, 2];

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
