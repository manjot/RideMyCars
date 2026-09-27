<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $table = 'wallet_transactions';

    protected $fillable = [
        'user_id',
        'transaction_ref',
        'type',
        'amount',
        'currency',
        'balance_before',
        'balance_after',
        'description',
        'status',
        'reference_id',
        'reference_type',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'float',
        'balance_before' => 'float',
        'balance_after' => 'float',
        'metadata' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($trx) {
            if (empty($trx->transaction_ref)) {
                $trx->transaction_ref = 'WTX-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
