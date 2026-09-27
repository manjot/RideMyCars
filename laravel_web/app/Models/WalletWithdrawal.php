<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WalletWithdrawal extends Model
{
    use HasFactory;

    protected $table = 'wallet_withdrawals';

    protected $fillable = [
        'withdrawal_ref',
        'user_id',
        'user_type',
        'amount',
        'currency',
        'fee',
        'net_amount',
        'payout_method',
        'payout_details',
        'status',
        'admin_notes',
        'rejection_reason',
        'transaction_reference',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'fee' => 'float',
        'net_amount' => 'float',
        'payout_details' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($withdrawal) {
            if (empty($withdrawal->withdrawal_ref)) {
                $withdrawal->withdrawal_ref = static::generateUniqueRef();
            }
            if ($withdrawal->net_amount === null) {
                $withdrawal->net_amount = max(0, (float)$withdrawal->amount - (float)($withdrawal->fee ?? 0));
            }
        });
    }

    public static function generateUniqueRef(): string
    {
        do {
            $ref = 'WTH-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (static::where('withdrawal_ref', $ref)->exists());
        return $ref;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Pending',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'warning',
        };
    }
}
