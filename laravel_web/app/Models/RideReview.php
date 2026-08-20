<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideReview extends Model
{
    protected $fillable = [
        'ride_id',
        'reviewer_id',
        'reviewee_id',
        'type',
        'rating',
        'comment',
    ];

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }
}
