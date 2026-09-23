<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display the audit trail. View-only by design — there is deliberately
     * no edit or delete route for these records, so the log stays a
     * trustworthy account of what actually happened.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest('created_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', $search.'%');
                    });
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', $request->action.'%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(25)->withQueryString();

        // Distinct action keys and the users who have any log entries, for
        // the filter dropdowns — cheap enough to compute on every request
        // given the modest scale of this cooperative's usage.
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $users = User::whereIn('id', ActivityLog::select('user_id')->whereNotNull('user_id')->distinct())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.activity-logs', compact('logs', 'actions', 'users'));
    }
}
