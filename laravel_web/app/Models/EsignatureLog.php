<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EsignatureLog extends Model
{
    use HasFactory;

    protected $table = 'esignature_logs';

    public $timestamps = false;

    protected $fillable = [
        'investor_id',
        'signer_name',
        'agreement_version',
        'governance_clause_accepted',
        'ip_address',
        'browser_user_agent',
        'signed_timestamp',
    ];

    protected $casts = [
        'governance_clause_accepted' => 'boolean',
        'signed_timestamp' => 'datetime',
    ];

    public function investor()
    {
        return $this->belongsTo(InvestorProfile::class, 'investor_id');
    }
}
