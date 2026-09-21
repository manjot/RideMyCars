<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountryComplianceRule extends Model
{
    use HasFactory;

    protected $table = 'country_compliance_rules';

    protected $fillable = [
        'country_code',
        'country_name',
        'regulatory_body',
        'regulatory_tier',
        'verification_gate_title',
        'verification_gate_description',
        'required_documents',
        'declarations',
        'compliance_text',
        'legal_notices',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'required_documents' => 'array',
        'declarations' => 'array',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('display_order', 'asc');
    }
}
