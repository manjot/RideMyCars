<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_customer_id',
        'provider_payment_method_id',
        'card_brand',
        'card_last4',
        'expiry_month',
        'expiry_year',
        'cardholder_name',
        'is_default',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'expiry_month' => 'integer',
        'expiry_year' => 'integer',
    ];

    /**
     * Get the user that owns the payment method.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include default payment methods.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get brand display name.
     */
    public function getBrandNameAttribute(): string
    {
        return match (strtolower($this->card_brand)) {
            'visa' => 'Visa',
            'mastercard', 'mc' => 'Mastercard',
            'amex', 'american_express' => 'American Express',
            'discover' => 'Discover',
            default => ucfirst($this->card_brand ?: 'Card'),
        };
    }

    /**
     * Get brand emoji icon.
     */
    public function getBrandIconAttribute(): string
    {
        return match (strtolower($this->card_brand)) {
            'visa' => '💳',
            'mastercard', 'mc' => '💳',
            'amex' => '💳',
            'discover' => '💳',
            default => '💳',
        };
    }
}
