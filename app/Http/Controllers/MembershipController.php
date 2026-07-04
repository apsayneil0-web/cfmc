<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MembershipController extends Controller
{
    /**
     * Display the membership registration page.
     */
    public function index(Request $request)
    {
        $crops = Crop::all();

        $query = Farmer::with('crop');

        // Default: hide archived records unless specifically filtered
        if (!$request->has('status') || $request->status == '') {
            $query->where('status', '!=', 'archived');
        }

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('middle_initial', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('suffix', 'like', '%' . $search . '%')
                  ->orWhere('contact_number', 'like', '%' . $search . '%')
                  ->orWhere('municipality', 'like', '%' . $search . '%')
                  ->orWhere('province', 'like', '%' . $search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $farmers = $query->orderBy('created_at', 'desc')->get();

        return view('manager.membership', compact('crops', 'farmers'));
    }

    /**
     * Store a new farmer membership request.
     */
    public function store(Request $request)
    {
        // Check for confirmation
        if (!$request->has('is_confirmed') || $request->input('is_confirmed') != '1') {
            return redirect()->route('manager.membership')
                ->with('error', 'Please confirm your submission.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:5',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'contact_number' => 'required|string|max:20',
            'crop_id' => 'required|exists:crops,id',
            'land_area' => 'required|numeric|min:0',
            'documents' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'province' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'barangay' => 'nullable|string|max:255',
        ]);

        // Handle document upload
        $documentsPath = null;
        if ($request->hasFile('documents')) {
            $document = $request->file('documents');
            $documentName = time() . '_' . $document->getClientOriginalName();
            $documentsPath = $document->storeAs('farmer_documents', $documentName, 'public');
        }

        // Create the farmer record
        $farmer = Farmer::create([
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'],
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'],
            'contact_number' => $validated['contact_number'],
            'crop_id' => $validated['crop_id'],
            'land_area' => $validated['land_area'],
            'documents_path' => $documentsPath,
            'province' => $validated['province'],
            'municipality' => $validated['municipality'],
            'barangay' => $validated['barangay'] ?? null,
            'status' => 'pending',
            'user_id' => Auth::check() ? Auth::id() : null,
        ]);

        return redirect()->route('manager.membership')
            ->with('success', 'Membership request submitted successfully!');
    }

    /**
     * Update farmer details.
     */
    public function update(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:5',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'contact_number' => 'required|string|max:20',
            'crop_id' => 'required|exists:crops,id',
            'land_area' => 'required|numeric|min:0',
            'province' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'documents' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $updateData = [
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'],
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'],
            'contact_number' => $validated['contact_number'],
            'crop_id' => $validated['crop_id'],
            'land_area' => $validated['land_area'],
            'province' => $validated['province'],
            'municipality' => $validated['municipality'],
        ];

        // Handle document removal
        if ($request->has('remove_document') && $request->input('remove_document') == '1') {
            if ($farmer->documents_path) {
                Storage::disk('public')->delete($farmer->documents_path);
                $updateData['documents_path'] = null;
            }
        }

        // Handle new document upload
        if ($request->hasFile('documents')) {
            // Delete old document if exists
            if ($farmer->documents_path) {
                Storage::disk('public')->delete($farmer->documents_path);
            }

            // Upload new document
            $document = $request->file('documents');
            $documentName = time() . '_' . $document->getClientOriginalName();
            $updateData['documents_path'] = $document->storeAs('farmer_documents', $documentName, 'public');
        }

        $farmer->update($updateData);

        return redirect()->route('manager.membership')
            ->with('success', 'Farmer updated successfully!');
    }

    /**
     * Archive farmer.
     */
    public function archive(Farmer $farmer)
    {
        $farmer->update(['status' => 'archived']);

        return redirect()->route('manager.membership')
            ->with('success', 'Farmer archived successfully!');
    }

    /**
     * Unarchive farmer.
     */
    public function unarchive(Farmer $farmer)
    {
        $farmer->update(['status' => 'pending']);

        return redirect()->route('manager.membership', ['status' => 'archived'])
            ->with('success', 'Farmer unarchived successfully!');
    }
}