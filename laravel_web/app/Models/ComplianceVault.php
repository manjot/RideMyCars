<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplianceVault extends Model
{
    use HasFactory;

    protected $table = 'compliance_vault';

    public $timestamps = false;

    protected $fillable = [
        'investor_id',
        'document_type',
        'document_title',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
        'status',
        'admin_feedback',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'file_size' => 'integer',
    ];

    public function investor()
    {
        return $this->belongsTo(InvestorProfile::class, 'investor_id');
    }

    public function getSecureDownloadUrlAttribute(): string
    {
        return route('investor.vault.download', ['id' => $this->id]);
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    public function getDocumentTypeNameAttribute(): string
    {
        return match ($this->document_type) {
            'CPA_LETTER' => 'Certified CPA Verification Letter / Wealth Statement',
            'GH_CARD' => 'National ID / Ghana Card / Gov ID',
            'TAX_TIN' => 'Corporate Tax Identification Number (TIN)',
            'PASSPORT' => 'International Passport Photo Identification',
            'GOV_ID' => 'Valid Government-Issued Photo Identification',
            'CORP_DOCS' => 'Corporate Registration / Certificate of Incorporation',
            'ADDRESS_PROOF' => 'Proof of Registered Address / Utility Statement',
            'ACCREDITED_RISK_ACK' => 'Accredited Investor Risk Acknowledgement (NI 45-106)',
            default => str_replace('_', ' ', $this->document_type),
        };
    }
}
