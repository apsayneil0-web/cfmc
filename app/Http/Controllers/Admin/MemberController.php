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
        $query = Farmer::with('crops')->whereIn('status', ['approved', 'archived']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw("CONCAT_WS(' ', first_name, middle_initial, last_name, suffix) LIKE ?", ["{$search}%"])
                    ->orWhere('last_name', 'like', "{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $members = $query->orderBy('created_at', 'desc')->get();

        return view('admin.members', compact('members'));
    }
}
