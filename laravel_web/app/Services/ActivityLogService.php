<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Log a user activity.
     */
    public static function log(string $activityType, string $description, ?int $userId = null, ?array $properties = []): ActivityLog
    {
        $userId = $userId ?? Auth::id();

        return ActivityLog::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => Request::ip(),
        ]);
    }
}
