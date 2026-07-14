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

        $request->merge([
            'contact_number' => preg_replace('/[\s\-]+/', '', (string) $request->input('contact_number')),
        ]);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:5',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'contact_number' => ['required', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'crop_id' => 'required|exists:crops,id',
            'land_area' => 'required|numeric|min:0',
            'documents' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'certificate_of_title' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'barangay_certification' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'rsbsa' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'province' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'barangay' => 'nullable|string|max:255',
        ], [
            'contact_number.regex' => 'Please enter a valid Philippine mobile number (e.g. 09123456789).',
        ]);

        // Create the farmer record
        $farmer = Farmer::create([
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'],
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'],
            'contact_number' => $validated['contact_number'],
            'crop_id' => $validated['crop_id'],
            'land_area' => $validated['land_area'],
            'documents_path' => $this->storeFarmerDocument($request, 'documents'),
            'certificate_of_title_path' => $this->storeFarmerDocument($request, 'certificate_of_title'),
            'barangay_certification_path' => $this->storeFarmerDocument($request, 'barangay_certification'),
            'rsbsa_path' => $this->storeFarmerDocument($request, 'rsbsa'),
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
        $request->merge([
            'contact_number' => preg_replace('/[\s\-]+/', '', (string) $request->input('contact_number')),
        ]);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:5',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'contact_number' => ['required', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'crop_id' => 'required|exists:crops,id',
            'land_area' => 'required|numeric|min:0',
            'province' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'documents' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'certificate_of_title' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'barangay_certification' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'rsbsa' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'contact_number.regex' => 'Please enter a valid Philippine mobile number (e.g. 09123456789).',
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
            'barangay' => $validated['barangay'] ?? null,
        ];

        $this->applyDocumentUpdate($request, $farmer, $updateData, 'documents', 'documents_path');
        $this->applyDocumentUpdate($request, $farmer, $updateData, 'certificate_of_title', 'certificate_of_title_path');
        $this->applyDocumentUpdate($request, $farmer, $updateData, 'barangay_certification', 'barangay_certification_path');
        $this->applyDocumentUpdate($request, $farmer, $updateData, 'rsbsa', 'rsbsa_path');

        $farmer->update($updateData);

        return redirect()->back()
            ->with('success', 'Farmer updated successfully!');
    }

    /**
     * Archive farmer.
     */
    public function archive(Farmer $farmer)
    {
        $farmer->update(['status' => 'archived']);

        return redirect()->back()
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

    /**
     * Store an uploaded farmer document under the given form field, if present.
     */
    private function storeFarmerDocument(Request $request, string $field): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $document = $request->file($field);
        $documentName = time().'_'.$document->getClientOriginalName();

        return $document->storeAs('farmer_documents', $documentName, 'public');
    }

    /**
     * Apply a removal and/or replacement for one of a farmer's document fields.
     */
    private function applyDocumentUpdate(Request $request, Farmer $farmer, array &$updateData, string $field, string $pathColumn): void
    {
        if ($request->boolean('remove_'.$field) && $farmer->{$pathColumn}) {
            Storage::disk('public')->delete($farmer->{$pathColumn});
            $updateData[$pathColumn] = null;
        }

        if ($request->hasFile($field)) {
            if ($farmer->{$pathColumn}) {
                Storage::disk('public')->delete($farmer->{$pathColumn});
            }

            $updateData[$pathColumn] = $this->storeFarmerDocument($request, $field);
        }
    }
}