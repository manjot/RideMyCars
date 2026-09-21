<?php

namespace App\Services;

use App\Models\ComplianceVault;
use App\Models\CountryComplianceRule;
use App\Models\InvestorAuditLog;
use App\Models\InvestorProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvestorComplianceService
{
    /**
     * Get compliance rules by country code (prefers DB rule if active and up-to-date, falls back to default definition)
     */
    public static function getCountryRules(string $countryCode): array
    {
        $code = strtoupper(trim($countryCode));

        // Attempt retrieval from database
        $dbRule = CountryComplianceRule::where('country_code', $code)
            ->where('is_active', true)
            ->first();

        if ($dbRule && !str_contains($dbRule->verification_gate_title ?? '', 'Eminsang') && !str_contains(json_encode($dbRule->declarations ?? []), 'Eminsang')) {
            return [
                'country_code' => $dbRule->country_code,
                'country_name' => $dbRule->country_name,
                'regulatory_body' => $dbRule->regulatory_body,
                'regulatory_tier' => $dbRule->regulatory_tier,
                'verification_gate_title' => $dbRule->verification_gate_title,
                'verification_gate_description' => $dbRule->verification_gate_description,
                'required_documents' => $dbRule->required_documents ?? [],
                'declarations' => $dbRule->declarations ?? [],
                'compliance_text' => $dbRule->compliance_text,
                'legal_notices' => $dbRule->legal_notices,
            ];
        }

        return self::getDefaultRules($code);
    }

    /**
     * Get standard dynamic default rules matching regulatory specifications
     */
    public static function getDefaultRules(string $countryCode): array
    {
        $code = strtoupper(trim($countryCode));

        return match ($code) {
            'USA', 'US' => [
                'country_code' => 'USA',
                'country_name' => 'United States',
                'regulatory_body' => 'Securities & Exchange Commission (SEC)',
                'regulatory_tier' => 'US_REG_D',
                'verification_gate_title' => 'Regulation D, Rule 506(c) Accreditation Gate',
                'verification_gate_description' => 'Requires 3rd-party proof of Accredited Investor status ($200k+ income or $1M+ net worth excluding primary home).',
                'required_documents' => [
                    [
                        'type' => 'CPA_LETTER',
                        'title' => 'Certified CPA Letter / Wealth Statement',
                        'description' => 'Official letter from licensed CPA, registered attorney, broker-dealer, or qualified wealth verification statement (dated within 90 days).',
                        'required' => true,
                    ],
                    [
                        'type' => 'GOV_ID',
                        'title' => 'Valid Government Photo ID',
                        'description' => 'US State Driver License or US Passport.',
                        'required' => true,
                    ]
                ],
                'declarations' => [
                    [
                        'id' => 'income_check',
                        'label' => 'My individual income exceeded $200,000 (or $300,000 with spouse) in each of the two most recent years.',
                        'required' => false,
                    ],
                    [
                        'id' => 'net_worth_check',
                        'label' => 'My individual net worth (or joint net worth with spouse) exceeds $1,000,000, excluding the value of my primary residence.',
                        'required' => false,
                    ],
                    [
                        'id' => 'rule_506c_ack',
                        'label' => 'I acknowledge that securities offered under SEC Rule 506(c) are restricted private placement securities with mandatory holding requirements.',
                        'required' => true,
                    ]
                ],
                'compliance_text' => 'Securities offered under Regulation D, Rule 506(c) are restricted private placement securities. Independent 3rd-party verification is required.',
                'legal_notices' => 'Securities offered through this portal have not been registered under the U.S. Securities Act of 1933, as amended.',
            ],

            'CAN', 'CA' => [
                'country_code' => 'CAN',
                'country_name' => 'Canada',
                'regulatory_body' => 'Provincial Securities Commissions (OSC, BCSC)',
                'regulatory_tier' => 'CA_NI_45_106',
                'verification_gate_title' => 'National Instrument 45-106 Exemption Verification',
                'verification_gate_description' => 'Verification under Canadian National Instrument 45-106 prospectus exemptions and risk disclosure execution.',
                'required_documents' => [
                    [
                        'type' => 'PASSPORT',
                        'title' => 'Canadian Passport or Provincial Photo ID',
                        'description' => 'Valid Canadian passport or provincial driver license.',
                        'required' => true,
                    ],
                    [
                        'type' => 'ACCREDITED_RISK_ACK',
                        'title' => 'Signed NI 45-106 Risk Acknowledgement Form',
                        'description' => 'Schedule Form 45-106F9 Risk Acknowledgement for Canadian accredited individual/corporate purchasers.',
                        'required' => true,
                    ]
                ],
                'declarations' => [
                    [
                        'id' => 'ni_45_106_ack',
                        'label' => 'I confirm that I am an Accredited Investor under section 1.1 of National Instrument 45-106 Prospectus Exemptions.',
                        'required' => true,
                    ],
                    [
                        'id' => 'can_risk_ack',
                        'label' => 'I have reviewed and agree to the Accredited Investor Risk Acknowledgement terms.',
                        'required' => true,
                    ]
                ],
                'compliance_text' => 'Canadian private placement exemption pursuant to National Instrument 45-106.',
                'legal_notices' => 'These securities have not been qualified by a prospectus filed with any Canadian securities administrator.',
            ],

            'GBR', 'UK', 'GB' => [
                'country_code' => 'GBR',
                'country_name' => 'United Kingdom',
                'regulatory_body' => 'Financial Conduct Authority (FCA)',
                'regulatory_tier' => 'UK_FCA',
                'verification_gate_title' => 'FCA Certified Sophisticated / High-Net-Worth Gate',
                'verification_gate_description' => 'Self-certification gate as a High-Net-Worth Individual or Certified Sophisticated Investor under Financial Services and Markets Act 2000 (FPO rules).',
                'required_documents' => [
                    [
                        'type' => 'PASSPORT',
                        'title' => 'UK Passport or Valid Photo ID',
                        'description' => 'Valid British passport or UK driver license.',
                        'required' => true,
                    ],
                    [
                        'type' => 'ADDRESS_PROOF',
                        'title' => 'Proof of UK Residence',
                        'description' => 'Utility bill or bank statement dated within the last 3 months.',
                        'required' => true,
                    ]
                ],
                'declarations' => [
                    [
                        'id' => 'fca_sophisticated_ack',
                        'label' => 'I certify that I am a Certified Sophisticated Investor / High-Net-Worth Individual under FCA / Financial Promotion Order (FPO) guidelines with the capacity to evaluate early-stage technology and fleet infrastructure ventures.',
                        'required' => true,
                    ]
                ],
                'compliance_text' => 'FCA FPO Article 48 / Article 50 exemption route for Certified Sophisticated Investors.',
                'legal_notices' => 'Content on this portal has not been approved by an authorized person under Section 21 of the Financial Services and Markets Act 2000 (FSMA).',
            ],

            'EU' => [
                'country_code' => 'EU',
                'country_name' => 'European Union',
                'regulatory_body' => 'European Securities & Markets Authority (ESMA)',
                'regulatory_tier' => 'EU_ESMA',
                'verification_gate_title' => 'Prospectus Regulation Article 1(4) Exemptions Gate',
                'verification_gate_description' => 'Classification as a Professional Client or Qualified Institutional Buyer to legally bypass expensive regional prospectus filing fees.',
                'required_documents' => [
                    [
                        'type' => 'PASSPORT',
                        'title' => 'EU Member State Passport or National ID',
                        'description' => 'Valid national identity card or European Union passport.',
                        'required' => true,
                    ],
                    [
                        'type' => 'CORP_DOCS',
                        'title' => 'Entity Registration or Professional Client Certification',
                        'description' => 'Commercial register extract or letter establishing professional client standing.',
                        'required' => true,
                    ]
                ],
                'declarations' => [
                    [
                        'id' => 'eu_professional_ack',
                        'label' => 'I certify that I am classified as a Professional Client or Qualified Institutional Buyer under MiFID II and EU Prospectus Regulation Article 1(4).',
                        'required' => true,
                    ]
                ],
                'compliance_text' => 'Exempt private placement pursuant to EU Regulation (EU) 2017/1129 Article 1(4).',
                'legal_notices' => 'This opportunity is exempt from the requirement to publish a prospectus under Article 1(4) of the EU Prospectus Regulation.',
            ],

            'GHA', 'GH' => [
                'country_code' => 'GHA',
                'country_name' => 'Ghana',
                'regulatory_body' => 'Securities & Exchange Commission (SEC Ghana)',
                'regulatory_tier' => 'GH_SEC',
                'verification_gate_title' => 'SEC / Exempt Private Placement Routing (Ride My Cars (Ghana))',
                'verification_gate_description' => 'Institutional or verified High-Net-Worth individuals. Validated Ghana Card/TIN check via Ride My Cars (Ghana).',
                'required_documents' => [
                    [
                        'type' => 'GH_CARD',
                        'title' => 'Validated Ghana Card / Government Photo ID',
                        'description' => 'National ID card (ECOWAS Ghana Card) or Ghanaian Passport.',
                        'required' => true,
                    ],
                    [
                        'type' => 'TAX_TIN',
                        'title' => 'Corporate Tax Identification Number (TIN) / Proof of TIN',
                        'description' => 'Ghana Revenue Authority (GRA) Tax Identification Number certificate or card.',
                        'required' => true,
                    ]
                ],
                'declarations' => [
                    [
                        'id' => 'gh_sec_hnw_ack',
                        'label' => 'I confirm that I am an institutional or verified High-Net-Worth participant participating under Ghana SEC private placement exemptions.',
                        'required' => true,
                    ],
                    [
                        'id' => 'gh_rmc_ack',
                        'label' => 'I acknowledge and agree that local escrow custody and capital remittance tracking are conducted in coordination with Ride My Cars (Ghana).',
                        'required' => true,
                    ]
                ],
                'compliance_text' => 'Regulated private placement routing adhering to Bank of Ghana foreign exchange and SEC Ghana directives.',
                'legal_notices' => 'All local financial processing, local compliance tracking, and escrow custody operations are managed securely through regional corporate frameworks in coordination with Ride My Cars (Ghana).',
            ],

            'AFRICA' => [
                'country_code' => 'AFRICA',
                'country_name' => 'Other African Nations',
                'regulatory_body' => 'Respective National SECs (e.g., Nigeria, Kenya, South Africa)',
                'regulatory_tier' => 'AFRICA_REGIONAL',
                'verification_gate_title' => 'Regional SEC / Cross-Border Investment Protocols',
                'verification_gate_description' => 'Cross-border capital remittance routing checking for local central bank compliance and capital repatriation codes.',
                'required_documents' => [
                    [
                        'type' => 'PASSPORT',
                        'title' => 'National Passport or Resident ID Card',
                        'description' => 'Valid international passport or national identity document.',
                        'required' => true,
                    ],
                    [
                        'type' => 'TAX_TIN',
                        'title' => 'National Tax ID Certificate',
                        'description' => 'Tax registration document from national revenue authority.',
                        'required' => true,
                    ]
                ],
                'declarations' => [
                    [
                        'id' => 'africa_central_bank_ack',
                        'label' => 'I confirm that all proposed funds comply with local central bank foreign exchange regulations and anti-money laundering (AML) guidelines.',
                        'required' => true,
                    ]
                ],
                'compliance_text' => 'Cross-border remittance routing through Pan-African corporate escrow channels.',
                'legal_notices' => 'Investments are subject to local national securities board guidelines and Central Bank capital repatriation codes.',
            ],

            default => [
                'country_code' => 'ROW',
                'country_name' => 'Rest of World',
                'regulatory_body' => 'International Securities & Private Placement Standard',
                'regulatory_tier' => 'ROW',
                'verification_gate_title' => 'International Private Placement Gate',
                'verification_gate_description' => 'Accredited and institutional private placement routing for international jurisdictions.',
                'required_documents' => [
                    [
                        'type' => 'PASSPORT',
                        'title' => 'Valid Passport Photo Identification',
                        'description' => 'Official government passport with clearly visible photo and expiration date.',
                        'required' => true,
                    ],
                    [
                        'type' => 'ADDRESS_PROOF',
                        'title' => 'Proof of Physical Residence',
                        'description' => 'Recent bank statement or utility bill (within 90 days).',
                        'required' => true,
                    ]
                ],
                'declarations' => [
                    [
                        'id' => 'row_private_placement_ack',
                        'label' => 'I confirm that I am eligible to participate in private placement offerings under the securities regulations of my country of citizenship and residence.',
                        'required' => true,
                    ]
                ],
                'compliance_text' => 'Non-public international private placement.',
                'legal_notices' => 'The information contained on this web portal does not constitute an offer to sell in any jurisdiction where prohibited by law.',
            ]
        };
    }

    /**
     * Securely store an uploaded document in the private vault directory.
     */
    public static function storeDocument(InvestorProfile $investor, UploadedFile $file, string $documentType, ?string $title = null): ComplianceVault
    {
        $folder = 'investor_vault/' . $investor->investor_id;
        $extension = $file->getClientOriginalExtension();
        $safeFileName = Str::slug($documentType) . '_' . time() . '_' . Str::random(8) . '.' . $extension;

        // Store file in private local disk
        $path = $file->storeAs($folder, $safeFileName, 'local');

        $doc = ComplianceVault::create([
            'investor_id' => $investor->id,
            'document_type' => $documentType,
            'document_title' => $title ?: str_replace('_', ' ', $documentType),
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'PENDING',
            'uploaded_at' => now(),
        ]);

        self::logAction(
            $investor->id,
            $investor->user_id,
            'DOC_UPLOADED',
            "Uploaded document: {$doc->document_title} ({$doc->original_filename})",
            ['vault_id' => $doc->id, 'document_type' => $documentType]
        );

        return $doc;
    }

    /**
     * Log an action in the Investor Audit Trail
     */
    public static function logAction(?int $investorId, ?int $userId, string $action, string $description, ?array $payload = null): InvestorAuditLog
    {
        return InvestorAuditLog::create([
            'investor_id' => $investorId,
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'System',
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }
}
