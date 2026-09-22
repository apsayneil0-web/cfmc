<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\Farmer;
use App\Models\Staff;
use App\Models\User;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Roles that require an email address (Admin, Manager). Farmer (3) does not.
     */
    private const EMAIL_REQUIRED_ROLES = [1, 2];

    /**
     * Roles that get a Staff profile record (Admin, Manager). Farmer (3) has
     * its own membership record (the `farmers` table) instead.
     */
    private const STAFF_ROLES = [1, 2];

    /**
     * Store a newly created user in database. Unlike the manager's user
     * management (which only creates Farmer accounts), this can create
     * Admin, Manager, or Farmer accounts.
     */
    public function store(Request $request)
    {
        $roleID = (int) $request->input('roleID');

        // Farmer accounts go through the same "register now, approve
        // immediately" flow as the manager's version of this screen — a
        // full membership record plus an auto-provisioned login account.
        // Admin/Manager accounts keep the existing Staff-profile flow below.
        if ($roleID === 3) {
            return $this->storeFarmer($request);
        }

        $isStaffRole = in_array($roleID, self::STAFF_ROLES, true);

        $validator = Validator::make($request->all(), [
            'roleID' => 'required|in:1,2',
            'first_name' => [Rule::requiredIf($isStaffRole), 'nullable', 'string', 'max:255'],
            'middle_name' => 'nullable|string|max:255',
            'last_name' => [Rule::requiredIf($isStaffRole), 'nullable', 'string', 'max:255'],
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:Male,Female',
            'profile_picture' => 'nullable|image|max:2048',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'Phonenumber' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // The Staff record is the single source of truth for the person's
        // name; the account's display name is derived from it, not re-typed.
        $name = implode(' ', array_filter([$request->first_name, $request->middle_name, $request->last_name], fn ($part) => filled($part)));

        try {
            $user = User::create([
                'name' => $name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                // The system marks accounts active automatically once they actually log in.
                'status' => 'inactive',
                'roleID' => $roleID,
                'Phonenumber' => $request->Phonenumber ?? null,
                'firstTimelogin' => true,
                'isloggedin' => false,
                'FailedLoginAttemps' => 0,
            ]);

            Staff::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'date_of_birth' => $request->date_of_birth,
                'age' => $request->date_of_birth ? Carbon::parse($request->date_of_birth)->age : null,
                'gender' => $request->gender,
                'profile_picture' => $this->storeProfilePicture($request),
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully!'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register a new farmer and immediately provision their login account,
     * in one step — identical in behavior to the manager's version of this
     * form. The record goes straight to "approved" (no separate pending/
     * review step), since whoever is filling this out has already verified
     * the person and their documents in person.
     */
    private function storeFarmer(Request $request)
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

                $username = $this->generateFarmerUsername($farmer);
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

            app(SmsService::class)->send(
                $validated['contact_number'],
                "Welcome to CFMC! Your account is ready. Username: {$result['username']} Password: {$result['password']} Please log in and change your password."
            );

            return response()->json([
                'success' => true,
                'message' => "{$result['name']}'s account was created. Username: {$result['username']}, temporary password: {$result['password']}. Credentials have been texted to the farmer.",
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
     * "juan.delacruz2" on collision).
     */
    private function generateFarmerUsername(Farmer $farmer): string
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
     * Store an uploaded staff profile picture, if present.
     */
    private function storeProfilePicture(Request $request): ?string
    {
        if (! $request->hasFile('profile_picture')) {
            return null;
        }

        $file = $request->file('profile_picture');
        $fileName = time().'_'.$file->getClientOriginalName();

        return $file->storeAs('staff_profiles', $fileName, 'public');
    }

    /**
     * Display a listing of all users (Admin, Manager, and Farmer accounts).
     */
    public function index(Request $request)
    {
        $query = User::with(['role', 'staff']);

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
        } else {
            // Archived accounts are kept out of the default view — they're only
            // ever reachable by explicitly filtering the Status dropdown to Archived.
            $query->where('status', '!=', 'archived');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Crop options for the "Create Account" form's Farmer crop-type checkboxes
        $crops = Crop::all();

        return view('admin.user-management', compact('users', 'crops'));
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
     * Activate or deactivate a Manager or Farmer account. Admin accounts are
     * exempt — an admin can't lock out another admin (or themselves) this way.
     * Locked and archived accounts have their own dedicated flows.
     */
    public function toggleStatus(User $user)
    {
        if ((int) $user->roleID === 1) {
            return response()->json([
                'success' => false,
                'message' => 'Admin accounts cannot be activated or deactivated.'
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
     * Admin-initiated password reset for a Manager or Farmer account. Admin
     * accounts are excluded on purpose — one admin overwriting another
     * admin's credentials from this screen would be a takeover vector, so an
     * admin who's locked out has to go through the normal forgot-password flow.
     */
    public function changePassword(Request $request, User $user)
    {
        if (! in_array((int) $user->roleID, [2, 3], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Only Manager and Farmer accounts can have their password changed here.'
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
                // Kept in the clear (same as the temp-password relay already used
                // elsewhere) so the admin can hand it to the account holder directly.
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
            'user' => $user->load('staff')
        ]);
    }

    /**
     * Update user details, including role.
     */
    public function update(Request $request, User $user)
    {
        $roleID = (int) $request->input('roleID');
        $isStaffRole = in_array($roleID, self::STAFF_ROLES, true);

        $validator = Validator::make($request->all(), [
            'roleID' => 'required|in:1,2,3',
            'name' => [Rule::requiredIf(! $isStaffRole), 'nullable', 'string', 'max:255'],
            'first_name' => [Rule::requiredIf($isStaffRole), 'nullable', 'string', 'max:255'],
            'middle_name' => 'nullable|string|max:255',
            'last_name' => [Rule::requiredIf($isStaffRole), 'nullable', 'string', 'max:255'],
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:Male,Female',
            'profile_picture' => 'nullable|image|max:2048',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => [
                Rule::requiredIf(in_array($roleID, self::EMAIL_REQUIRED_ROLES, true)),
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
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
            $email = $request->email;
            if (empty($email)) {
                $email = $request->username . '@farm.local';
            }

            $name = $isStaffRole
                ? implode(' ', array_filter([$request->first_name, $request->middle_name, $request->last_name], fn ($part) => filled($part)))
                : $request->name;

            $updateData = [
                'name' => $name,
                'username' => $request->username,
                'email' => $email,
                'roleID' => $roleID,
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

            if ($isStaffRole) {
                $staffData = [
                    'first_name' => $request->first_name,
                    'middle_name' => $request->middle_name,
                    'last_name' => $request->last_name,
                    'date_of_birth' => $request->date_of_birth,
                    'age' => $request->date_of_birth ? Carbon::parse($request->date_of_birth)->age : null,
                    'gender' => $request->gender,
                ];

                if ($request->hasFile('profile_picture')) {
                    if ($user->staff?->profile_picture) {
                        Storage::disk('public')->delete($user->staff->profile_picture);
                    }
                    $staffData['profile_picture'] = $this->storeProfilePicture($request);
                }

                Staff::updateOrCreate(['user_id' => $user->id], $staffData);
            }

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
