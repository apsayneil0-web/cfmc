<?php

namespace App\Http\Controllers;

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
            'status' => 'required|in:active,inactive',
            'role' => 'required|in:farmer,manager',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Determine role ID based on selection
        $roleID = $request->role === 'manager' ? 2 : 3;

        // If email is empty for farmer, generate a placeholder
        $email = $request->email;
        if (empty($email) && $request->role === 'farmer') {
            $email = $request->username . '@farm.local';
        }

        // Check if email is required for manager
        if (empty($email) && $request->role === 'manager') {
            return response()->json([
                'success' => false,
                'message' => 'Email is required for manager accounts.'
            ], 422);
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
        $users = User::with('role')->orderBy('created_at', 'desc')->paginate(10);
        return view('manager.user-management', compact('users'));
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