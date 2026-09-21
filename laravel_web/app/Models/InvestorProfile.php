<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InvestorProfile extends Model
{
    use HasFactory;

    protected $table = 'investor_profiles';

    protected $fillable = [
        'user_id',
        'legal_name',
        'email',
        'phone_number',
        'country_residence',
        'country_code',
        'address',
        'city',
        'state',
        'postal_code',
        'entity_type',
        'tax_id_or_national_id',
        'regulatory_tier',
        'accreditation_details',
        'selected_tranche',
        'equity_percentage',
        'capital_commitment_ghc',
        'capital_commitment_usd',
        'remittance_method',
        'verification_status',
        'payment_unlocked',
        'payment_reference_code',
        'admin_notes',
        'rejection_reason',
        'document_request_notes',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'accreditation_details' => 'array',
        'equity_percentage' => 'decimal:2',
        'capital_commitment_ghc' => 'decimal:2',
        'capital_commitment_usd' => 'decimal:2',
        'payment_unlocked' => 'boolean',
        'verified_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($profile) {
            if (empty($profile->payment_reference_code)) {
                $profile->payment_reference_code = 'NDFG-INV-' . date('Y') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function getInvestorIdAttribute()
    {
        return $this->id;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function documents()
    {
        return $this->hasMany(ComplianceVault::class, 'investor_id');
    }

    public function esignatureLog()
    {
        return $this->hasOne(EsignatureLog::class, 'investor_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(InvestorAuditLog::class, 'investor_id')->latest();
    }

    public function investmentPlan()
    {
        return $this->belongsTo(InvestmentPlan::class, 'selected_tranche', 'tranche_code');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereIn('verification_status', ['PENDING', 'UNDER_REVIEW']);
    }

    public function scopeApproved($query)
    {
        return $query->where('verification_status', 'APPROVED');
    }

    public function scopeRejected($query)
    {
        return $query->where('verification_status', 'REJECTED');
    }

    public function scopeNeedsMoreDocs($query)
    {
        return $query->where('verification_status', 'NEED_MORE_DOCS');
    }

    // Helpers & Accessors
    public function isVerified(): bool
    {
        return $this->verification_status === 'APPROVED';
    }

    public function isPending(): bool
    {
        return in_array($this->verification_status, ['PENDING', 'UNDER_REVIEW']);
    }

    public function needsMoreDocs(): bool
    {
        return $this->verification_status === 'NEED_MORE_DOCS';
    }

    public function isRejected(): bool
    {
        return $this->verification_status === 'REJECTED';
    }

    public function isPaymentAccessible(): bool
    {
        return $this->isVerified() && $this->payment_unlocked;
    }

    public function getFormattedCommitmentGhcAttribute(): string
    {
        return number_format((float) $this->capital_commitment_ghc, 2) . ' GHC';
    }

    public function getFormattedCommitmentUsdAttribute(): string
    {
        return '$' . number_format((float) $this->capital_commitment_usd, 2);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->verification_status) {
            'APPROVED' => 'success',
            'PENDING' => 'warning',
            'UNDER_REVIEW' => 'info',
            'NEED_MORE_DOCS' => 'danger',
            'REJECTED' => 'danger',
            default => 'gray',
        };
    }
}
