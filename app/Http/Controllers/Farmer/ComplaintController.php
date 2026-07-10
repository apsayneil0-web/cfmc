<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('farmer.complaints', compact('complaints'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'action' => 'required|in:draft,submit',
        ]);

        Complaint::create([
            'user_id' => Auth::id(),
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'status' => $validated['action'] === 'submit' ? 'submitted' : 'draft',
        ]);

        $message = $validated['action'] === 'submit'
            ? 'Complaint submitted for review!'
            : 'Complaint saved as draft.';

        return redirect()->route('farmer.complaints')->with('success', $message);
    }

    public function update(Request $request, Complaint $complaint)
    {
        abort_if($complaint->user_id !== Auth::id(), 403);
        abort_if($complaint->status !== 'draft', 422, 'Only draft complaints can be edited.');

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'action' => 'required|in:draft,submit',
        ]);

        $complaint->update([
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'status' => $validated['action'] === 'submit' ? 'submitted' : 'draft',
        ]);

        return redirect()->route('farmer.complaints')->with('success', 'Complaint updated successfully!');
    }

    public function destroy(Complaint $complaint)
    {
        abort_if($complaint->user_id !== Auth::id(), 403);
        abort_if($complaint->status !== 'draft', 422, 'Only draft complaints can be deleted.');

        $complaint->delete();

        return redirect()->route('farmer.complaints')->with('success', 'Draft complaint deleted.');
    }
}
