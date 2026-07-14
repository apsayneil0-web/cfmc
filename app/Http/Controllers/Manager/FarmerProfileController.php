<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\Farmer;
use Illuminate\Http\Request;

class FarmerProfileController extends Controller
{
    /**
     * Display the roster of approved farmer members. Editing and archiving
     * reuse MembershipController's routes, since it's the same Farmer record.
     */
    public function index(Request $request)
    {
        $crops = Crop::all();

        $query = Farmer::with(['crop', 'account'])
            ->where('status', 'approved');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_initial', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('municipality', 'like', "%{$search}%")
                    ->orWhere('province', 'like', "%{$search}%");
            });
        }

        $farmers = $query->orderBy('last_name')->orderBy('first_name')->get();

        return view('manager.farmer-profile', compact('crops', 'farmers'));
    }
}
