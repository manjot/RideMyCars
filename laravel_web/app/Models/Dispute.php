<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispute_code',
        'user_id',
        'service_type',
        'booking_reference',
        'category',
        'description',
        'evidence_photo_url',
        'contact_email',
        'contact_phone',
        'status',
        'is_within_72h',
        'event_completed_at',
        'deadline_at',
        'admin_notes',
        'resolved_at',
    ];

    protected $casts = [
        'is_within_72h' => 'boolean',
        'event_completed_at' => 'datetime',
        'deadline_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate whether a given date/time is within the 72-hour dispute window.
     */
    public static function checkWithin72Hours(?\DateTimeInterface $completedAt): bool
    {
        if (!$completedAt) {
            return true;
        }

        return now()->diffInHours($completedAt, false) >= -72;
    }
}
