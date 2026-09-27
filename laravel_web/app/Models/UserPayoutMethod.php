<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPayoutMethod extends Model
{
    use HasFactory;

    protected $table = 'user_payout_methods';

    protected $fillable = [
        'user_id',
        'type',
        'is_default',
        'bank_name',
        'account_holder_name',
        'account_number',
        'routing_code',
        'branch_name',
        'momo_network',
        'momo_phone',
        'momo_account_name',
        'metadata',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getDisplayTitleAttribute(): string
    {
        if ($this->type === 'momo') {
            return ($this->momo_network ? strtoupper($this->momo_network) . ' MoMo: ' : 'MoMo: ') . ($this->momo_phone ?? '');
        }

        $last4 = $this->account_number ? substr($this->account_number, -4) : '••••';
        return ($this->bank_name ?? 'Bank Account') . ' (•••• ' . $last4 . ')';
    }

    public function getFormattedDetailsAttribute(): array
    {
        if ($this->type === 'momo') {
            return [
                'type' => 'momo',
                'network' => $this->momo_network,
                'phone' => $this->momo_phone,
                'account_name' => $this->momo_account_name,
            ];
        }

        return [
            'type' => 'bank_account',
            'bank_name' => $this->bank_name,
            'account_holder_name' => $this->account_holder_name,
            'account_number' => $this->account_number,
            'routing_code' => $this->routing_code,
            'branch_name' => $this->branch_name,
        ];
    }
}
