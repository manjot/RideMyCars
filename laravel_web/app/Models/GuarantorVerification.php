<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuarantorVerification extends Model
{
    protected $fillable = [
        'driver_profile_id',
        'full_name',
        'ghana_card_number',
        'dob',
        'relationship',
        'primary_phone',
        'alt_phone',
        'digital_address',
        'physical_address',
        'employer_business',
        'job_title',
        'workplace_address',
        'ghana_card_front_url',
        'ghana_card_back_url',
        'signed_liability_agreement_url',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function driverProfile()
    {
        return $this->belongsTo(DriverProfile::class);
    }
}
