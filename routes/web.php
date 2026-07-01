<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MachineryController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\UserManagerController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
});

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('forgot-password.form');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('forgot-password');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('reset-password.form');
Route::post('/reset-password/{token}', [ForgotPasswordController::class, 'resetPassword'])->name('reset-password');

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| DASHBOARD (REAL CONTROLLER NOW)
|--------------------------------------------------------------------------
*/

Route::get('/dashboard/admin', function () {
    return view('admin.dashboard');
})->middleware('auth');

Route::get('/dashboard/manager', [DashboardController::class, 'index'])
    ->middleware('auth');

Route::get('/dashboard/farmer', function () {
    return view('farmer.dashboard');
})->middleware('auth');

/*
|--------------------------------------------------------------------------
| MODULES (NOW REAL CRUD ROUTES)
|--------------------------------------------------------------------------
*/

// Farmers
Route::resource('farmers', FarmerController::class)->middleware('auth');

// Loans
Route::resource('loans', LoanController::class)->middleware('auth');

// Payments
Route::resource('payments', PaymentController::class)->middleware('auth');

// Machinery
Route::resource('machinery', MachineryController::class)->middleware('auth');

// Schedules
Route::resource('schedules', ScheduleController::class)->middleware('auth');

// Complaints
Route::resource('complaints', ComplaintController::class)->middleware('auth');

/*
|--------------------------------------------------------------------------
| STATIC PAGES (OPTIONAL - you can remove later)
|--------------------------------------------------------------------------
*/

Route::get('/membership', function () {
    $crops = \App\Models\Crop::all();
    $farmers = \App\Models\Farmer::with('crop')->latest()->get();
    return view('manager.membership', compact('crops', 'farmers'));
})->middleware('auth');

Route::post('/membership', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'barangay' => 'required|string|max:255',
        'municipality' => 'required|string|max:255',
        'province' => 'required|string|max:255',
        'contact_number' => 'required|string|max:20',
        'crop_type' => 'required|exists:crops,id',
        'land_area' => 'required|numeric|min:0',
        'documents' => 'nullable|array',
        'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
    ]);

    // Handle file uploads - save to public/photo/documents
    $documentPaths = [];
    if ($request->hasFile('documents')) {
        try {
            $uploadPath = public_path('photo/documents');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            foreach ($request->file('documents') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);
                $documentPaths[] = $filename;
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error uploading documents: ' . $e->getMessage());
        }
    }

    $farmer = \App\Models\Farmer::create([
        'first_name' => $request->first_name,
        'middle_initial' => $request->middle_initial,
        'last_name' => $request->last_name,
        'suffix' => $request->suffix,
        'barangay' => $request->barangay,
        'municipality' => $request->municipality,
        'province' => $request->province,
        'contact_number' => $request->contact_number,
        'crop_type' => $request->crop_type,
        'land_area' => $request->land_area,
        'documents' => $documentPaths,
        'status' => 'Pending Approval',
    ]);

    return redirect()->back()->with('success', 'Membership request created successfully!');
})->middleware('auth')->name('membership.store');

Route::post('/membership/update', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'farmer_id' => 'required|exists:farmers,id',
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'barangay' => 'required|string|max:255',
        'municipality' => 'required|string|max:255',
        'province' => 'required|string|max:255',
        'contact_number' => 'required|string|max:20',
        'crop_type' => 'required|exists:crops,id',
        'land_area' => 'required|numeric|min:0',
        'documents' => 'nullable|array',
        'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'removed_documents' => 'nullable|string',
    ]);

    $farmer = \App\Models\Farmer::findOrFail($request->farmer_id);

    // Handle file uploads - save to public/photo/documents
    $documentPaths = $farmer->documents ?? [];

    // Handle removed documents
    $removedDocs = $request->input('removed_documents');
    if ($removedDocs) {
        $removedDocsArray = explode(',', $removedDocs);
        $uploadPath = public_path('photo/documents');

        foreach ($removedDocsArray as $docToRemove) {
            $docToRemove = trim($docToRemove);
            if ($docToRemove) {
                // Remove from array
                $documentPaths = array_filter($documentPaths, function($doc) use ($docToRemove) {
                    return $doc !== $docToRemove;
                });

                // Delete file from storage
                $filePath = $uploadPath . '/' . $docToRemove;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        // Reindex array
        $documentPaths = array_values($documentPaths);
    }

    // Handle new document uploads
    if ($request->hasFile('documents')) {
        try {
            $uploadPath = public_path('photo/documents');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            foreach ($request->file('documents') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);
                $documentPaths[] = $filename;
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error uploading documents: ' . $e->getMessage());
        }
    }

    $farmer->update([
        'first_name' => $request->first_name,
        'middle_initial' => $request->middle_initial,
        'last_name' => $request->last_name,
        'suffix' => $request->suffix,
        'barangay' => $request->barangay,
        'municipality' => $request->municipality,
        'province' => $request->province,
        'contact_number' => $request->contact_number,
        'crop_type' => $request->crop_type,
        'land_area' => $request->land_area,
        'documents' => $documentPaths,
    ]);

    return redirect()->back()->with('success', 'Membership request updated successfully!');
})->middleware('auth')->name('membership.update');

Route::post('/membership/{id}/archive', function (\Illuminate\Http\Request $request, $id) {
    $farmer = \App\Models\Farmer::findOrFail($id);
    $farmer->update(['status' => 'Archived']);

    return response()->json(['success' => true]);
})->middleware('auth')->name('membership.archive');

Route::view('/farmer-profile', 'manager.farmer-profile')->middleware('auth');
Route::view('/loan-request', 'manager.loan-request')->middleware('auth');
Route::view('/loan-management', 'manager.loan-management')->middleware('auth');
Route::view('/financial', 'manager.financial')->middleware('auth');
Route::view('/announcements', 'manager.announcements')->middleware('auth');
Route::view('/reports', 'manager.reports')->middleware('auth');

// User management (manager/admin)
Route::get('/manager/users', [UserManagerController::class, 'index'])->middleware('auth');
Route::get('/manager/users/{id}', [UserManagerController::class, 'show'])->middleware('auth')->whereNumber('id');
Route::get('/manager/users/create', [UserManagerController::class, 'create'])->middleware('auth');
Route::post('/manager/users', [UserManagerController::class, 'store'])->middleware('auth');