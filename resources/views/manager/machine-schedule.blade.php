@extends('manager.layout')

@section('title', 'Machine Scheduling')
@section('header', 'Machine Rental Scheduling')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
    <!-- Header Actions -->
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option>July 2026</option>
                <option>August 2026</option>
                <option>September 2026</option>
            </select>
            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option>All Machines</option>
                <option>Harvester</option>
                <option>Tractor</option>
                <option>Pump Boat</option>
            </select>
        </div>
        <div class="flex items-center gap-3">
            <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-calendar-week mr-2"></i>Week
            </button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Add Schedule
            </button>
        </div>
    </div>

    <!-- Calendar View -->
    <div class="p-6">
        <div class="grid grid-cols-7 gap-2 mb-4">
            <div class="text-center text-xs font-medium text-gray-500 py-2">Sun</div>
            <div class="text-center text-xs font-medium text-gray-500 py-2">Mon</div>
            <div class="text-center text-xs font-medium text-gray-500 py-2">Tue</div>
            <div class="text-center text-xs font-medium text-gray-500 py-2">Wed</div>
            <div class="text-center text-xs font-medium text-gray-500 py-2">Thu</div>
            <div class="text-center text-xs font-medium text-gray-500 py-2">Fri</div>
            <div class="text-center text-xs font-medium text-gray-500 py-2">Sat</div>
        </div>
        <div class="grid grid-cols-7 gap-2">
            @for($i = 0; $i < 7; $i++)
            <div class="min-h-[120px] border border-gray-200 rounded-lg p-2 bg-gray-50">
                <p class="text-xs font-medium text-gray-500 mb-2">{{ $i + 1 }}</p>
                @if($i == 2)
                <div class="bg-blue-100 text-blue-700 text-xs p-2 rounded mb-1">
                    <p class="font-medium">Juan - Harvester</p>
                    <p class="text-xs">8AM-12PM</p>
                </div>
                <div class="bg-green-100 text-green-700 text-xs p-2 rounded">
                    <p class="font-medium">Maria - Tractor</p>
                    <p class="text-xs">1PM-5PM</p>
                </div>
                @endif
            </div>
            @endfor
            @for($i = 7; $i < 14; $i++)
            <div class="min-h-[120px] border border-gray-200 rounded-lg p-2">
                <p class="text-xs font-medium text-gray-500 mb-2">{{ $i + 1 }}</p>
            </div>
            @endfor
            @for($i = 14; $i < 21; $i++)
            <div class="min-h-[120px] border border-gray-200 rounded-lg p-2">
                <p class="text-xs font-medium text-gray-500 mb-2">{{ $i + 1 }}</p>
                @if($i == 18)
                <div class="bg-purple-100 text-purple-700 text-xs p-2 rounded">
                    <p class="font-medium">Pedro - Pump</p>
                    <p class="text-xs">6AM-9AM</p>
                </div>
                @endif
            </div>
            @endfor
            @for($i = 21; $i < 28; $i++)
            <div class="min-h-[120px] border border-gray-200 rounded-lg p-2">
                <p class="text-xs font-medium text-gray-500 mb-2">{{ $i + 1 }}</p>
            </div>
            @endfor
            @for($i = 28; $i < 31; $i++)
            <div class="min-h-[120px] border border-gray-200 rounded-lg p-2">
                <p class="text-xs font-medium text-gray-500 mb-2">{{ $i + 1 }}</p>
            </div>
            @endfor
        </div>
    </div>
</div>

<!-- Schedule List -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Upcoming Schedules</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Schedule ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Farmer Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">SCH-001</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Juan Dela Cruz</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Harvester</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 10, 2026 - 8:00 AM</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Member</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Approved</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg" title="Reschedule">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                            <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Archive">
                                <i class="fas fa-archive"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">SCH-002</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Maria Santos</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Tractor</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 12, 2026 - 7:00 AM</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Member</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Approved</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg" title="Reschedule">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                            <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Archive">
                                <i class="fas fa-archive"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">SCH-003</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Roberto Tan</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Harvester</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 15, 2026 - 9:00 AM</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Non-member</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Archive">
                                <i class="fas fa-archive"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection