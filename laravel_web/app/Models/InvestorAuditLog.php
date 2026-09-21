<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestorAuditLog extends Model
{
    use HasFactory;

    protected $table = 'investor_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'investor_id',
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function investor()
    {
        return $this->belongsTo(InvestorProfile::class, 'investor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
