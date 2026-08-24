<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutLedger extends Model
{
    protected $fillable = [
        'payout_ref',
        'user_id',
        'payment_transaction_id',
        'service_vertical',
        'gross_amount',
        'platform_fee',
        'maintenance_fee',
        'net_payout',
        'payout_method',
        'account_details',
        'status',
        'failure_reason',
        'retry_count',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentTransaction()
    {
        return $this->belongsTo(PaymentTransaction::class);
    }
}
