<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserManagerController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $roleFilter = $request->query('role');

        $roles = DB::table('role')->select('RoleID', 'RoleName')->get();

        $usersQuery = DB::table('users')
            ->select('users.*', 'role.RoleName')
            ->leftJoin('role', 'users.RoleID', '=', 'role.RoleID')
            ->orderBy('users.id', 'desc');

        if ($q) {
            $usersQuery->where(function ($w) use ($q) {
                $w->where('users.username', 'like', "%{$q}%")
                  ->orWhere('users.email', 'like', "%{$q}%");
            });
        }

        if ($roleFilter) {
            // allow filtering by RoleName or RoleID
            if (is_numeric($roleFilter)) {
                $usersQuery->where('users.RoleID', (int) $roleFilter);
            } else {
                $usersQuery->where('role.RoleName', $roleFilter);
            }
        }

        $users = $usersQuery->paginate(15)->withQueryString();

        // counts per role for categorization
        $roleCounts = DB::table('users')
            ->select('role.RoleID', 'role.RoleName', DB::raw('count(users.id) as total'))
            ->leftJoin('role', 'users.RoleID', '=', 'role.RoleID')
            ->groupBy('role.RoleID', 'role.RoleName')
            ->get()
            ->keyBy('RoleName');

        return view('manager.users', [
            'users' => $users,
            'roles' => $roles,
            'q' => $q,
            'roleFilter' => $roleFilter,
            'roleCounts' => $roleCounts,
        ]);
    }

    public function create()
    {
        $current = Auth::user();
        // only managers or admins can access create form
        if (! $current || ! in_array($current->RoleID, [1, 2])) {
            abort(403);
        }

        // Restrict choices: Managers can create Manager (2) and Farmer (3)
        $roles = DB::table('role')->whereIn('RoleID', [2,3])->get();

        return view('manager.user-create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $current = Auth::user();
        if (! $current || ! in_array($current->RoleID, [1, 2])) {
            abort(403);
        }

        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',
        ]);

        // Map incoming role value to RoleID. Accept either RoleName or numeric RoleID.
        $roleInput = $request->input('role');
        if (is_numeric($roleInput)) {
            $roleId = (int) $roleInput;
        } else {
            $roleRow = DB::table('role')->where('RoleName', $roleInput)->first();
            $roleId = $roleRow->RoleID ?? null;
        }

        // Only allow creating Manager (2) or Farmer (3)
        if (! in_array($roleId, [2,3])) {
            return back()->with('error', 'Invalid role selection');
        }

        // Prevent non-admins from creating Managers? Requirement allows Manager to create Manager and Farmer
        // So both RoleID 1 (Admin) and 2 (Manager) can create.

        $userData = [
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'RoleID' => $roleId,
            'Status' => 'Active',
            'PhoneNumber' => $request->input('phone') ?? null,
            'FirstTimeLogin' => 1,
        ];

        $user = \App\Models\User::create($userData);

        return redirect('/manager/users')->with('message', 'User created successfully');
    }

    public function show($id)
    {
        $user = DB::table('users')
            ->select('users.*', 'role.RoleName')
            ->leftJoin('role', 'users.RoleID', '=', 'role.RoleID')
            ->where('users.id', $id)
            ->first();

        if (! $user) {
            return redirect('/manager/users')->with('error', 'User not found');
        }

        // mask full password when displaying: show first 8 chars then ellipsis
        $maskedPassword = '********';
        $hashedPreview = null;
        if (! empty($user->password)) {
            $hashedPreview = substr($user->password, 0, 8) . '...';
        }

        return view('manager.user-show', [
            'user' => $user,
            'maskedPassword' => $maskedPassword,
            'hashedPreview' => $hashedPreview,
        ]);
    }
}
