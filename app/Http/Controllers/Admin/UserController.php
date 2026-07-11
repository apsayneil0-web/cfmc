<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Roles that require an email address (Admin, Manager). Farmer (3) does not.
     */
    private const EMAIL_REQUIRED_ROLES = [1, 2];

    /**
     * Store a newly created user in database. Unlike the manager's user
     * management (which only creates Farmer accounts), this can create
     * Admin, Manager, or Farmer accounts.
     */
    public function store(Request $request)
    {
        $roleID = (int) $request->input('roleID');

        $validator = Validator::make($request->all(), [
            'roleID' => 'required|in:1,2,3',
            'name' => 'required|string|max:255',
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
            'status' => 'required|in:active,inactive',
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

        try {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $email,
                'password' => Hash::make($request->password),
                'status' => $request->status,
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
     * Display a listing of all users (Admin, Manager, and Farmer accounts).
     */
    public function index()
    {
        $users = User::with('role')->orderBy('created_at', 'desc')->paginate(10);

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
            'user' => $user
        ]);
    }

    /**
     * Update user details, including role.
     */
    public function update(Request $request, User $user)
    {
        $roleID = (int) $request->input('roleID');

        $validator = Validator::make($request->all(), [
            'roleID' => 'required|in:1,2,3',
            'name' => 'required|string|max:255',
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

            $user->update([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $email,
                'roleID' => $roleID,
                'Phonenumber' => $request->Phonenumber ?? null,
                'status' => $request->status,
            ]);

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
