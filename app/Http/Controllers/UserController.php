<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Models\Farmer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Register a new farmer and immediately provision their login account,
     * in one step. Unlike the admin's Membership Approval workflow (where a
     * farmer's own application sits as "pending" until an admin reviews it),
     * a manager filling this out has already verified the person and their
     * documents in person, so the record goes straight to "approved" and the
     * account is created right away — no separate admin review step.
     */
    public function store(Request $request)
    {
        $request->merge([
            'contact_number' => preg_replace('/[\s\-]+/', '', (string) $request->input('contact_number')),
        ]);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:5',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'contact_number' => ['required', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'crop_ids' => 'required|array|min:1',
            'crop_ids.*' => 'exists:crops,id',
            'land_area' => 'required|numeric|min:0',
            'documents' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'certificate_of_title' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'barangay_certification' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'rsbsa' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'province' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'barangay' => 'nullable|string|max:255',
        ], [
            'contact_number.regex' => 'Please enter a valid Philippine mobile number (e.g. 09123456789).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $validated = $validator->validated();

        try {
            $result = DB::transaction(function () use ($request, $validated) {
                $farmer = Farmer::create([
                    'first_name' => $validated['first_name'],
                    'middle_initial' => $validated['middle_initial'] ?? null,
                    'last_name' => $validated['last_name'],
                    'suffix' => $validated['suffix'] ?? null,
                    'contact_number' => $validated['contact_number'],
                    'land_area' => $validated['land_area'],
                    'documents_path' => $this->storeFarmerDocument($request, 'documents'),
                    'certificate_of_title_path' => $this->storeFarmerDocument($request, 'certificate_of_title'),
                    'barangay_certification_path' => $this->storeFarmerDocument($request, 'barangay_certification'),
                    'rsbsa_path' => $this->storeFarmerDocument($request, 'rsbsa'),
                    'province' => $validated['province'],
                    'municipality' => $validated['municipality'],
                    'barangay' => $validated['barangay'] ?? null,
                    'status' => 'approved',
                ]);

                $farmer->crops()->sync($validated['crop_ids']);

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

                return ['name' => $farmer->full_name, 'username' => $username, 'password' => $password];
            });

            return response()->json([
                'success' => true,
                'message' => "{$result['name']}'s account was created. Username: {$result['username']}, temporary password: {$result['password']}. Please share these with them directly.",
                'name' => $result['name'],
                'username' => $result['username'],
                'password' => $result['password'],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create farmer account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build a unique username from the farmer's name (e.g. "juan.delacruz",
     * "juan.delacruz2" on collision). Mirrors the same generation used when
     * an admin approves a farmer's own membership application.
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
     * Store an uploaded farmer document under the given form field.
     */
    private function storeFarmerDocument(Request $request, string $field): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $document = $request->file($field);
        $documentName = time().'_'.$document->getClientOriginalName();

        return $document->storeAs('farmer_documents', $documentName, 'public');
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('role')->where('roleID', '!=', 2);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search.'%')
                    ->orWhere('name', 'like', '% '.$search.'%')
                    ->orWhere('username', 'like', $search.'%')
                    ->orWhere('email', 'like', $search.'%')
                    ->orWhere('Phonenumber', 'like', $search.'%');
            });
        }

        if ($request->filled('role')) {
            $query->where('roleID', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Crop options for the "Register New Farmer" form's crop-type checkboxes
        $crops = Crop::all();

        return view('manager.user-management', compact('users', 'crops'));
    }

    /**
     * Archive a user.
     */
    public function archive(User $user)
    {
        try {
            $user->update(['status' => 'archived']);
            return response()->json([
                'success' => true,
                'message' => 'User archived successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore an archived account back to active use.
     */
    public function unarchive(User $user)
    {
        if ($user->status !== 'archived') {
            return response()->json([
                'success' => false,
                'message' => 'This account is not archived.'
            ], 422);
        }

        try {
            $user->update([
                'status' => 'active',
                'FailedLoginAttemps' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User unarchived successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unarchive user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unlock a farmer account that was locked after too many failed login
     * attempts, and reset its failed-attempt count so it doesn't instantly
     * re-lock on the next mistyped password.
     */
    public function unlock(User $user)
    {
        if ($user->status !== 'locked') {
            return response()->json([
                'success' => false,
                'message' => 'This account is not locked.'
            ], 422);
        }

        try {
            $user->update([
                'status' => 'active',
                'FailedLoginAttemps' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User unlocked successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unlock user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate or deactivate a farmer account. Locked and archived accounts
     * have their own dedicated flows (unlock / edit), so this only toggles
     * between active and inactive.
     */
    public function toggleStatus(User $user)
    {
        if ((int) $user->roleID !== 3) {
            return response()->json([
                'success' => false,
                'message' => 'Only farmer accounts can be activated or deactivated here.'
            ], 422);
        }

        if (! in_array($user->status, ['active', 'inactive'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Only active or inactive accounts can be toggled this way.'
            ], 422);
        }

        try {
            $newStatus = $user->status === 'active' ? 'inactive' : 'active';
            $user->update(['status' => $newStatus]);

            return response()->json([
                'success' => true,
                'message' => 'User '.($newStatus === 'active' ? 'activated' : 'deactivated').' successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manager-initiated password reset for a farmer account (e.g. the farmer
     * forgot their password and has no way to self-service a reset). Scoped
     * to Farmer accounts only — a manager must never be able to overwrite an
     * Admin's or another Manager's credentials from this screen.
     */
    public function changePassword(Request $request, User $user)
    {
        if ((int) $user->roleID !== 3) {
            return response()->json([
                'success' => false,
                'message' => 'Only farmer accounts can have their password changed here.'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $user->update([
                'password' => Hash::make($request->password),
                // Kept in the clear (same as a farmer's initial account setup) so
                // the manager can relay it to the farmer directly from this screen.
                'temp_password' => $request->password,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display user details.
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * Update user details.
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'nullable|email',
            'Phonenumber' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,locked',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $updateData = [
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'Phonenumber' => $request->Phonenumber ?? null,
                'status' => $request->status,
            ];

            // Unlocking a farmer account (moving it off "locked") must also clear
            // its failed-attempt count, or the very next mistyped password would
            // instantly re-lock it.
            if ($user->status === 'locked' && $request->status !== 'locked') {
                $updateData['FailedLoginAttemps'] = 0;
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }
}