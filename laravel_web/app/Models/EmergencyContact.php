<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'relationship',
        'is_primary',
        'notify_sms',
        'notify_whatsapp',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'notify_sms' => 'boolean',
        'notify_whatsapp' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
