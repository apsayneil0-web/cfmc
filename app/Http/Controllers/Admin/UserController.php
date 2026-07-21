<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
            'username' => 'required|string|max:255|unique:users,username',
            'email' => [
                Rule::requiredIf(in_array($roleID, self::EMAIL_REQUIRED_ROLES, true)),
                'nullable',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => 'required|string|min:6',
            'Phonenumber' => 'nullable|string|max:20',
            'farmer_id' => 'nullable|integer|exists:farmers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Farmer accounts may omit an email; keep the column populated with a placeholder
        $email = $request->email;
        if (empty($email)) {
            $email = $request->username . '@farm.local';
        }

        // For Admin/Manager, the Staff record is the single source of truth for the
        // person's name; the account's display name is derived from it, not re-typed.
        $name = $isStaffRole
            ? implode(' ', array_filter([$request->first_name, $request->middle_name, $request->last_name], fn ($part) => filled($part)))
            : $request->name;

        try {
            $user = User::create([
                'name' => $name,
                'username' => $request->username,
                'email' => $email,
                'password' => Hash::make($request->password),
                // The system marks accounts active automatically once they actually log in.
                'status' => 'inactive',
                'roleID' => $roleID,
                'Phonenumber' => $request->Phonenumber ?? null,
                'firstTimelogin' => true,
                'isloggedin' => false,
                'FailedLoginAttemps' => 0,
            ]);

            // Link the account to its approved membership record, if one was selected
            if ($roleID === 3 && $request->filled('farmer_id')) {
                Farmer::where('id', $request->farmer_id)
                    ->where('status', 'approved')
                    ->whereNull('account_user_id')
                    ->update(['account_user_id' => $user->id]);
            }

            if ($isStaffRole) {
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
            }

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
    public function index()
    {
        $users = User::with(['role', 'staff'])->orderBy('created_at', 'desc')->paginate(10);

        // Approved membership records without an account yet, for the "Create Account" name picker
        $availableFarmers = Farmer::where('status', 'approved')
            ->whereNull('account_user_id')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_initial', 'last_name', 'suffix', 'contact_number']);

        return view('admin.user-management', compact('users', 'availableFarmers'));
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

            $user->update([
                'name' => $name,
                'username' => $request->username,
                'email' => $email,
                'roleID' => $roleID,
                'Phonenumber' => $request->Phonenumber ?? null,
                'status' => $request->status,
            ]);

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
