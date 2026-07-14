<?php

namespace App\Http\Controllers;

use App\Models\Farmer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Store a newly created user in database.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|email',
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

        // Accounts created here are always Farmer accounts
        $roleID = 3;

        // If email is empty, generate a placeholder
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
                // The system marks accounts active automatically once they actually log in.
                'status' => 'inactive',
                'roleID' => $roleID,
                'Phonenumber' => $request->Phonenumber ?? null,
                'firstTimelogin' => true,
                'isloggedin' => false,
                'FailedLoginAttemps' => 0,
            ]);

            // Link the account to its approved membership record, if one was selected
            if ($request->filled('farmer_id')) {
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
     * Display a listing of users.
     */
    public function index()
    {
        $users = User::with('role')->where('roleID', '!=', 2)->orderBy('created_at', 'desc')->paginate(10);

        // Approved membership records without an account yet, for the "Create Account" name picker
        $availableFarmers = Farmer::where('status', 'approved')
            ->whereNull('account_user_id')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_initial', 'last_name', 'suffix', 'contact_number']);

        return view('manager.user-management', compact('users', 'availableFarmers'));
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
            $user->update([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
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