<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * Record one audit-trail entry. Called explicitly at the point in a
     * controller/middleware where something meaningful actually happened —
     * deliberately not wired to generic model events, so every entry reads
     * as a sentence a human wrote ("Manager Juan approved Maria's loan")
     * instead of a raw field-change dump.
     *
     * @param  User|null  $user  Who did it. Null for the rare event with no
     *                           authenticated actor (e.g. a failed login
     *                           attempt against a username that doesn't exist).
     * @param  string  $action  Short machine key, e.g. "loan.approved".
     * @param  string  $description  Human-readable sentence for the log table.
     * @param  Model|null  $subject  What it was about (a Farmer, a User...).
     * @param  array  $properties  Optional extra context. Never put a raw
     *                             password or other secret in here — this
     *                             table is a readable audit trail, not a
     *                             secure vault.
     */
    public static function log(
        ?User $user,
        string $action,
        string $description,
        ?Model $subject = null,
        array $properties = []
    ): ActivityLog {
        $request = request();

        return ActivityLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
