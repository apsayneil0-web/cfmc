<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Farmer;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    private const PURPOSES = ['Information', 'Meeting', 'Reminder', 'Resolution'];

    /**
     * Display all announcements the Manager has created or drafted.
     */
    public function index(Request $request)
    {
        $query = Announcement::withCount('recipients')->whereNull('archived_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $announcements = $query->orderBy('created_at', 'desc')->get();

        $farmers = Farmer::where('status', 'approved')->orderBy('first_name')->get();

        return view('manager.announcement', [
            'announcements' => $announcements,
            'farmers' => $farmers,
            'purposes' => self::PURPOSES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateAnnouncement($request);

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'purpose' => $validated['purpose'],
            'resolution' => $validated['resolution'] ?? null,
            'announcement_date' => $validated['announcement_date'] ?? null,
            'time' => $validated['time'] ?? null,
            'location' => $validated['location'] ?? null,
            'audience' => $validated['audience'],
            'status' => $validated['status'],
            'created_by' => Auth::id(),
        ]);

        if ($validated['audience'] === 'selected') {
            $announcement->recipients()->sync($validated['farmer_ids'] ?? []);
        }

        $this->syncNotifications($announcement);

        return redirect()->route('manager.announcement')
            ->with('success', 'Announcement saved successfully.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $this->validateAnnouncement($request);

        $announcement->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'purpose' => $validated['purpose'],
            'resolution' => $validated['resolution'] ?? null,
            'announcement_date' => $validated['announcement_date'] ?? null,
            'time' => $validated['time'] ?? null,
            'location' => $validated['location'] ?? null,
            'audience' => $validated['audience'],
            'status' => $validated['status'],
        ]);

        $announcement->recipients()->sync(
            $validated['audience'] === 'selected' ? ($validated['farmer_ids'] ?? []) : []
        );

        $this->syncNotifications($announcement->refresh());

        return redirect()->route('manager.announcement')
            ->with('success', 'Announcement updated successfully.');
    }

    public function archive(Announcement $announcement)
    {
        $announcement->update(['archived_at' => now(), 'status' => 'archived']);
        $announcement->notifications()->delete();

        return redirect()->route('manager.announcement')
            ->with('success', 'Announcement archived.');
    }

    /**
     * Fan out (or retract) this announcement's notification rows so every
     * targeted recipient's bell reflects the current audience and content.
     * Existing recipients keep their read/unread state; only membership and
     * content are resynced.
     */
    private function syncNotifications(Announcement $announcement): void
    {
        if ($announcement->status !== 'published') {
            $announcement->notifications()->delete();
            return;
        }

        $targetUserIds = $announcement->resolveRecipientUserIds();

        $announcement->notifications()->whereNotIn('user_id', $targetUserIds)->delete();

        $announcement->notifications()->whereIn('user_id', $targetUserIds)->update([
            'title' => $announcement->title,
            'message' => $announcement->notification_message,
            'type' => $announcement->notification_type,
        ]);

        $existingUserIds = $announcement->notifications()->pluck('user_id');
        $newUserIds = $targetUserIds->diff($existingUserIds);

        $rows = $newUserIds->map(fn ($userId) => [
            'user_id' => $userId,
            'announcement_id' => $announcement->id,
            'title' => $announcement->title,
            'message' => $announcement->notification_message,
            'type' => $announcement->notification_type,
            'is_read' => false,
            'created_at' => now(),
        ])->all();

        if (!empty($rows)) {
            Notification::insert($rows);
        }
    }

    private function validateAnnouncement(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'purpose' => ['required', Rule::in(self::PURPOSES)],
            'resolution' => 'nullable|string',
            'announcement_date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'audience' => ['required', Rule::in(['all_members', 'selected'])],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'farmer_ids' => 'nullable|array',
            'farmer_ids.*' => 'exists:farmers,id',
        ]);
    }
}
