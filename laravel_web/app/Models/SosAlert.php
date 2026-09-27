<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SosAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ride_id',
        'role',
        'latitude',
        'longitude',
        'location_address',
        'status',
        'contacts_notified_count',
        'emergency_service_dialed',
        'notes',
        'resolved_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'contacts_notified_count' => 'integer',
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }
}
