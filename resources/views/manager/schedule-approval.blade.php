@extends('manager.layout')

@section('title', 'Schedule Approval')
@section('header', 'Schedule Approval')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <!-- Header Actions -->
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="relative">
                <input type="text" placeholder="Search requests..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="denied">Denied</option>
            </select>
            <input type="date" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Request ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Farmer Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Machine Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">SCH-001</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Juan Dela Cruz</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Harvester</td>
                    <td class="px-6 py-4 text-sm text-gray-500">July 10, 2026</td>
                    <td class="px-6 py-4 text-sm text-gray-500">8:00 AM</td>
                    <td class="px-6 py-4 text-sm text-gray-500">4 hours</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Check Availability">
                                <i class="fas fa-calendar-check"></i>
                            </button>
                            <button class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Deny">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">SCH-002</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Maria Santos</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Tractor</td>
                    <td class="px-6 py-4 text-sm text-gray-500">July 12, 2026</td>
                    <td class="px-6 py-4 text-sm text-gray-500">7:00 AM</td>
                    <td class="px-6 py-4 text-sm text-gray-500">6 hours</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Check Availability">
                                <i class="fas fa-calendar-check"></i>
                            </button>
                            <button class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Deny">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">SCH-003</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Pedro Reyes</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Pump Boat</td>
                    <td class="px-6 py-4 text-sm text-gray-500">July 08, 2026</td>
                    <td class="px-6 py-4 text-sm text-gray-500">6:00 AM</td>
                    <td class="px-6 py-4 text-sm text-gray-500">3 hours</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Approved</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-gray-400 cursor-not-allowed" disabled>
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">SCH-004</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Ana Garcia</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Harvester</td>
                    <td class="px-6 py-4 text-sm text-gray-500">July 05, 2026</td>
                    <td class="px-6 py-4 text-sm text-gray-500">8:00 AM</td>
                    <td class="px-6 py-4 text-sm text-gray-500">5 hours</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Denied</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-gray-400 cursor-not-allowed" disabled>
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing 1-10 of 18 entries</p>
        <div class="flex items-center gap-2">
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>Previous</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50">Next</button>
        </div>
    </div>
</div>

<!-- Schedule Details Modal -->
<div id="scheduleModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Schedule Request Details</h3>
            <button onclick="document.getElementById('scheduleModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Request ID</label>
                    <p class="text-sm font-medium text-gray-900">SCH-001</p>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Farmer Name</label>
                    <p class="text-sm font-medium text-gray-900">Juan Dela Cruz</p>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Machine Type</label>
                    <p class="text-sm font-medium text-gray-900">Harvester</p>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Member Status</label>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Member</span>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Scheduled Date</label>
                    <p class="text-sm font-medium text-gray-900">July 10, 2026</p>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Time</label>
                    <p class="text-sm font-medium text-gray-900">8:00 AM - 12:00 PM</p>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Farm Location</label>
                    <p class="text-sm font-medium text-gray-900">Brgy. San Jose</p>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Land Area</label>
                    <p class="text-sm font-medium text-gray-900">2.5 hectares</p>
                </div>
            </div>

            <!-- Machinery Availability -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Machinery Availability</h4>
                <div class="flex items-center gap-2 text-sm">
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <span class="text-gray-700">Available - Harvester #002</span>
                </div>
            </div>

            <!-- Remarks -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                <textarea rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Add remarks..."></textarea>
            </div>
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
            <button onclick="document.getElementById('scheduleModal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
            <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Deny</button>
            <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Approve</button>
        </div>
    </div>
</div>
@endsection