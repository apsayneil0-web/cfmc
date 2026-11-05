<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\StoresFarmerDocuments;
use App\Models\Crop;
use App\Models\Farmer;
use App\Models\Notification;
use App\Rules\PersonName;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MembershipController extends Controller
{
    use StoresFarmerDocuments;

    /**
     * Display the membership registration page.
     */
    public function index(Request $request)
    {
        $crops = Crop::all();

        $query = Farmer::with(['crops', 'activityLogs']);

        // Default to Pending when no status filter is chosen yet — that's the
        // actionable list a manager lands on this page to work through.
        // Explicitly picking "All Status" still excludes archived records.
        // Archived records live in their own view ("View Archived", ?archived=1).
        $showArchived = $request->boolean('archived') || $request->input('status') === 'archived';
        $status = $showArchived ? 'archived' : ($request->filled('status') ? $request->status : 'pending');

        if ($status === 'all') {
            $query->where('status', '!=', 'archived');
        } else {
            $query->where('status', $status);
        }

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereRaw("CONCAT_WS(' ', first_name, middle_initial, last_name, suffix) LIKE ?", ["{$search}%"]);
        }

        $farmers = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $archivedCount = Farmer::where('status', 'archived')->count();

        return view('manager.membership', compact('crops', 'farmers', 'showArchived', 'archivedCount'));
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
            'first_name' => ['required', 'string', 'max:255', new PersonName],
            'middle_initial' => ['nullable', 'string', 'max:5', 'regex:/^[\pL.]+$/u'],
            'last_name' => ['required', 'string', 'max:255', new PersonName],
            'suffix' => 'nullable|string|max:50',
            'contact_number' => ['required', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'crop_ids' => 'required|array|min:1',
            'crop_ids.*' => 'exists:crops,id',
            'land_area' => 'required|numeric|min:0',
            'documents' => 'required|array|min:1',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'certificate_of_title' => 'required|array|min:1',
            'certificate_of_title.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'barangay_certification' => 'required|array|min:1',
            'barangay_certification.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'rsbsa' => 'required|array|min:1',
            'rsbsa.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'province' => ['required', Rule::in([Farmer::PROVINCE])],
            'municipality' => ['required', Rule::in([Farmer::MUNICIPALITY])],
            'barangay' => ['nullable', Rule::in(Farmer::BARANGAYS)],
        ], [
            'contact_number.regex' => 'Please enter a valid Philippine mobile number (e.g. 09123456789).',
            'middle_initial.regex' => 'The middle initial may only contain letters.',
            'province.in' => 'Membership is only open to farmers in '.Farmer::MUNICIPALITY.', '.Farmer::PROVINCE.'.',
            'municipality.in' => 'Membership is only open to farmers in '.Farmer::MUNICIPALITY.', '.Farmer::PROVINCE.'.',
            'barangay.in' => 'Please select a barangay in '.Farmer::MUNICIPALITY.'.',
        ]);

        // Create the farmer record
        $farmer = Farmer::create([
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'],
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'],
            'contact_number' => $validated['contact_number'],
            'land_area' => $validated['land_area'],
            'documents_path' => $this->storeFarmerDocumentPaths($request, 'documents'),
            'certificate_of_title_path' => $this->storeFarmerDocumentPaths($request, 'certificate_of_title'),
            'barangay_certification_path' => $this->storeFarmerDocumentPaths($request, 'barangay_certification'),
            'rsbsa_path' => $this->storeFarmerDocumentPaths($request, 'rsbsa'),
            'province' => $validated['province'],
            'municipality' => $validated['municipality'],
            'barangay' => $validated['barangay'] ?? null,
            'status' => 'pending',
            'user_id' => Auth::check() ? Auth::id() : null,
        ]);

        $farmer->crops()->sync($validated['crop_ids']);

        Notification::notifyRoles(
            [1],
            'New Membership Application',
            "{$farmer->full_name}'s membership application is awaiting review.",
            'membership_application',
        );

        return redirect()->route('manager.membership')
            ->with('success', 'Membership request submitted successfully!');
    }

    /**
     * Location rules for editing/resubmitting an existing farmer — the same
     * Surallah-only lock store() enforces. The farmer's current barangay is
     * also accepted even if it isn't in Farmer::BARANGAYS, so older records
     * with a non-standard spelling can still be saved without losing it.
     */
    private function existingFarmerLocationRules(Farmer $farmer): array
    {
        $allowedBarangays = Farmer::BARANGAYS;
        if ($farmer->barangay && ! in_array($farmer->barangay, $allowedBarangays, true)) {
            $allowedBarangays[] = $farmer->barangay;
        }

        return [
            'province' => ['required', Rule::in([Farmer::PROVINCE])],
            'municipality' => ['required', Rule::in([Farmer::MUNICIPALITY])],
            'barangay' => ['nullable', Rule::in($allowedBarangays)],
        ];
    }

    private function locationMessages(): array
    {
        return [
            'province.in' => 'Membership is only open to farmers in '.Farmer::MUNICIPALITY.', '.Farmer::PROVINCE.'.',
            'municipality.in' => 'Membership is only open to farmers in '.Farmer::MUNICIPALITY.', '.Farmer::PROVINCE.'.',
            'barangay.in' => 'Please select a barangay in '.Farmer::MUNICIPALITY.'.',
        ];
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
            'first_name' => ['required', 'string', 'max:255', new PersonName],
            'middle_initial' => ['nullable', 'string', 'max:5', 'regex:/^[\pL.]+$/u'],
            'last_name' => ['required', 'string', 'max:255', new PersonName],
            'suffix' => 'nullable|string|max:50',
            'contact_number' => ['required', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'crop_ids' => 'required|array|min:1',
            'crop_ids.*' => 'exists:crops,id',
            'land_area' => 'required|numeric|min:0',
            ...$this->existingFarmerLocationRules($farmer),
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'certificate_of_title' => 'nullable|array',
            'certificate_of_title.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'barangay_certification' => 'nullable|array',
            'barangay_certification.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'rsbsa' => 'nullable|array',
            'rsbsa.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'contact_number.regex' => 'Please enter a valid Philippine mobile number (e.g. 09123456789).',
            'middle_initial.regex' => 'The middle initial may only contain letters.',
            ...$this->locationMessages(),
        ]);

        $updateData = [
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'],
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'],
            'contact_number' => $validated['contact_number'],
            'land_area' => $validated['land_area'],
            'province' => $validated['province'],
            'municipality' => $validated['municipality'],
            'barangay' => $validated['barangay'] ?? null,
        ];

        $this->applyFarmerDocumentUpdate($request, $farmer, $updateData, 'documents', 'documents_path');
        $this->applyFarmerDocumentUpdate($request, $farmer, $updateData, 'certificate_of_title', 'certificate_of_title_path');
        $this->applyFarmerDocumentUpdate($request, $farmer, $updateData, 'barangay_certification', 'barangay_certification_path');
        $this->applyFarmerDocumentUpdate($request, $farmer, $updateData, 'rsbsa', 'rsbsa_path');

        $farmer->update($updateData);
        $farmer->crops()->sync($validated['crop_ids']);

        return redirect()->back()
            ->with('success', 'Farmer updated successfully!');
    }

    /**
     * Correct a rejected membership application and send it back to the
     * Administrator, instead of making the Manager re-encode it (and
     * re-upload all 4 document sets) from scratch. Validation mirrors
     * update() — not store() — since documents stay optional here: unless a
     * replacement is uploaded, the farmer's existing files carry over. The
     * original rejection_reason is cleared from the live record, but
     * survives permanently in this farmer's ActivityLog entry (see
     * Admin\MembershipApprovalController::reject()).
     */
    public function resubmit(Request $request, Farmer $farmer)
    {
        abort_if($farmer->status !== 'rejected', 422, 'Only rejected applications can be resubmitted.');

        $request->merge([
            'contact_number' => preg_replace('/[\s\-]+/', '', (string) $request->input('contact_number')),
        ]);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255', new PersonName],
            'middle_initial' => ['nullable', 'string', 'max:5', 'regex:/^[\pL.]+$/u'],
            'last_name' => ['required', 'string', 'max:255', new PersonName],
            'suffix' => 'nullable|string|max:50',
            'contact_number' => ['required', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'crop_ids' => 'required|array|min:1',
            'crop_ids.*' => 'exists:crops,id',
            'land_area' => 'required|numeric|min:0',
            ...$this->existingFarmerLocationRules($farmer),
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'certificate_of_title' => 'nullable|array',
            'certificate_of_title.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'barangay_certification' => 'nullable|array',
            'barangay_certification.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'rsbsa' => 'nullable|array',
            'rsbsa.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'contact_number.regex' => 'Please enter a valid Philippine mobile number (e.g. 09123456789).',
            'middle_initial.regex' => 'The middle initial may only contain letters.',
            ...$this->locationMessages(),
        ]);

        $updateData = [
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'],
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'],
            'contact_number' => $validated['contact_number'],
            'land_area' => $validated['land_area'],
            'province' => $validated['province'],
            'municipality' => $validated['municipality'],
            'barangay' => $validated['barangay'] ?? null,
            'status' => 'pending',
            'rejection_reason' => null,
        ];

        $this->applyFarmerDocumentUpdate($request, $farmer, $updateData, 'documents', 'documents_path');
        $this->applyFarmerDocumentUpdate($request, $farmer, $updateData, 'certificate_of_title', 'certificate_of_title_path');
        $this->applyFarmerDocumentUpdate($request, $farmer, $updateData, 'barangay_certification', 'barangay_certification_path');
        $this->applyFarmerDocumentUpdate($request, $farmer, $updateData, 'rsbsa', 'rsbsa_path');

        $farmer->update($updateData);
        $farmer->crops()->sync($validated['crop_ids']);

        ActivityLogger::log(Auth::user(), 'membership.resubmitted', "Resubmitted {$farmer->full_name}'s membership application after correcting it.", $farmer);

        Notification::notifyRoles(
            [1],
            'Membership Application Resubmitted',
            "{$farmer->full_name}'s membership application has been corrected and resubmitted for review.",
            'membership_application',
        );

        return redirect()->back()
            ->with('success', "{$farmer->full_name}'s membership application has been corrected and resubmitted.");
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

}