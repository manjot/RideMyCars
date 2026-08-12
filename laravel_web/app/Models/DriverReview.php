<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverReview extends Model
{
    protected $fillable = [
        'driver_booking_id',
        'driver_profile_id',
        'client_id',
        'rating',
        'review_text',
    ];

    public function booking()
    {
        return $this->belongsTo(DriverBooking::class, 'driver_booking_id');
    }

    public function driverProfile()
    {
        return $this->belongsTo(DriverProfile::class, 'driver_profile_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    protected static function booted()
    {
        static::saved(function ($review) {
            $profile = $review->driverProfile;
            if ($profile) {
                $avg = DriverReview::where('driver_profile_id', $profile->id)->avg('rating');
                $profile->update([
                    'rating' => round($avg ?? 5.0, 2),
                ]);
            }
        });
    }
}
