<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentPlan extends Model
{
    use HasFactory;

    protected $table = 'investment_plans';

    protected $fillable = [
        'tranche_code',
        'tier_name',
        'capital_commitment_ghc',
        'capital_commitment_usd',
        'equity_percentage',
        'min_investment_usd',
        'max_investment_usd',
        'summary_headline',
        'description',
        'perks',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'capital_commitment_ghc' => 'decimal:2',
        'capital_commitment_usd' => 'decimal:2',
        'equity_percentage' => 'decimal:2',
        'min_investment_usd' => 'decimal:2',
        'max_investment_usd' => 'decimal:2',
        'perks' => 'array',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('display_order', 'asc');
    }

    public function investorProfiles()
    {
        return $this->hasMany(InvestorProfile::class, 'selected_tranche', 'tranche_code');
    }

    public function getFormattedGhcAttribute(): string
    {
        return number_format((float) $this->capital_commitment_ghc, 0) . ' GHC';
    }

    public function getFormattedUsdAttribute(): string
    {
        return '$' . number_format((float) $this->capital_commitment_usd, 0);
    }
}
