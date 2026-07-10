<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Display all registered (approved or archived) farmer records.
     */
    public function index(Request $request)
    {
        $query = Farmer::with('crop')->whereIn('status', ['approved', 'archived']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_initial', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('suffix', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $members = $query->orderBy('last_name')->get();

        return view('admin.members', compact('members'));
    }
}
