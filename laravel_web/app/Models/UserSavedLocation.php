<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSavedLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'address',
        'latitude',
        'longitude',
        'place_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
