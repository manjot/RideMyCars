<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'unit',
        'image',
        'status',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    protected $appends = [
        'formatted_price_with_unit',
        'display_price',
        'currency_symbol',
        'currency_code',
        'pricing_source',
    ];

    public static array $unitOptions = [
        'pc' => 'Piece (pc)',
        'kg' => 'Kilogram (kg)',
        'g' => 'Gram (g)',
        'L' => 'Liter (L)',
        'ml' => 'Milliliter (ml)',
        'box' => 'Box',
        'pack' => 'Pack',
        'dozen' => 'Dozen',
        'bottle' => 'Bottle',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getResolvedPricingAttribute(): array
    {
        return \App\Services\PricingService::resolvePrice((float) $this->price);
    }

    public function getDisplayPriceAttribute(): float
    {
        return (float) ($this->resolved_pricing['display_price'] ?? $this->price);
    }

    public function getCurrencySymbolAttribute(): string
    {
        return $this->resolved_pricing['currency_symbol'] ?? '$';
    }

    public function getCurrencyCodeAttribute(): string
    {
        return $this->resolved_pricing['currency_code'] ?? 'USD';
    }

    public function getPricingSourceAttribute(): string
    {
        return $this->resolved_pricing['pricing_source'] ?? 'default_usd';
    }

    public function getFormattedPriceWithUnitAttribute(): string
    {
        $resolved = $this->resolved_pricing;
        return ($resolved['formatted_price'] ?? ('$' . number_format($this->price, 2))) . ' / ' . $this->unit;
    }
}
