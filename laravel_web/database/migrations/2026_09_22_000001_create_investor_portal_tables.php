<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Investment Plans Table (Admin-configurable tranches)
        Schema::create('investment_plans', function (Blueprint $table) {
            $table->id();
            $table->char('tranche_code', 1)->unique(); // 'A', 'B', 'C'
            $table->string('tier_name', 100); // 'Seed Tier', 'Growth Tier', 'Venture Tier'
            $table->decimal('capital_commitment_ghc', 14, 2); // 720000.00, 1440000.00, 2640000.00
            $table->decimal('capital_commitment_usd', 14, 2); // 60000.00, 120000.00, 220000.00
            $table->decimal('equity_percentage', 5, 2); // 10.00, 14.00, 22.00
            $table->decimal('min_investment_usd', 14, 2)->default(10000.00);
            $table->decimal('max_investment_usd', 14, 2)->nullable();
            $table->string('summary_headline')->nullable();
            $table->text('description')->nullable();
            $table->json('perks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(1);
            $table->timestamps();
        });

        // 2. Country Compliance Rules Table (Admin-configurable country rules)
        Schema::create('country_compliance_rules', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 10)->unique(); // 'USA', 'CAN', 'GBR', 'EU', 'GHA', 'AFRICA', 'ROW'
            $table->string('country_name', 100);
            $table->string('regulatory_body', 200); // e.g., 'Securities & Exchange Commission (SEC)'
            $table->string('regulatory_tier', 100); // e.g., 'US_REG_D', 'UK_FCA', 'GH_SEC'
            $table->string('verification_gate_title', 255);
            $table->text('verification_gate_description')->nullable();
            $table->json('required_documents')->nullable(); // Array of document types & labels
            $table->json('declarations')->nullable(); // Array of self-certification questions & checkboxes
            $table->text('compliance_text')->nullable();
            $table->text('legal_notices')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(1);
            $table->timestamps();
        });

        // 3. Investor Profiles Table (Core user identities, location, tranche, status)
        Schema::create('investor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('legal_name', 255);
            $table->string('email', 255)->unique();
            $table->string('phone_number', 50);
            $table->string('country_residence', 100);
            $table->string('country_code', 10)->default('USA');
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 50)->nullable();
            $table->string('entity_type', 50)->default('individual'); // 'individual', 'corporate', 'institutional'
            $table->string('tax_id_or_national_id', 100)->nullable(); // Ghana Card, US SSN/EIN, TIN
            
            // Regulatory routing
            $table->string('regulatory_tier', 100)->default('US_REG_D');
            $table->json('accreditation_details')->nullable(); // Answers to dynamic compliance questions
            
            // Investment Allocation
            $table->char('selected_tranche', 1)->default('A'); // 'A', 'B', 'C'
            $table->decimal('equity_percentage', 5, 2)->default(10.00); // 10.00, 14.00, 22.00
            $table->decimal('capital_commitment_ghc', 14, 2)->default(720000.00);
            $table->decimal('capital_commitment_usd', 14, 2)->default(60000.00);
            $table->string('remittance_method', 100)->default('wire_swift'); // 'wire_swift', 'local_bank_eminsang', 'mobile_money'
            
            // Verification State
            $table->string('verification_status', 30)->default('PENDING'); // 'PENDING', 'UNDER_REVIEW', 'NEED_MORE_DOCS', 'APPROVED', 'REJECTED'
            $table->boolean('payment_unlocked')->default(false);
            $table->string('payment_reference_code', 50)->nullable()->unique(); // e.g., 'NDFG-INV-2026-XXXX'
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('document_request_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            $table->index('verification_status');
            $table->index('country_code');
            $table->index('selected_tranche');
        });

        // 4. Compliance Vault Table (Secure document storage with strict access control)
        Schema::create('compliance_vault', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_id')->constrained('investor_profiles')->cascadeOnDelete();
            $table->string('document_type', 100); // 'CPA_LETTER', 'GH_CARD', 'TAX_TIN', 'PASSPORT', 'GOV_ID', 'CORP_DOCS', 'ADDRESS_PROOF', 'ACCREDITED_RISK_ACK', 'OTHER'
            $table->string('document_title', 255)->nullable();
            $table->string('original_filename', 255);
            $table->text('file_path'); // Encrypted / private storage relative path
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('status', 30)->default('PENDING'); // 'PENDING', 'VERIFIED', 'REJECTED', 'REUPLOAD_REQUESTED'
            $table->text('admin_feedback')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
            
            $table->index(['investor_id', 'document_type']);
        });

        // 5. e-Signature Logs Table (Immutable digital audit trail of signed NDFG LLC Operating Agreement)
        Schema::create('esignature_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_id')->constrained('investor_profiles')->cascadeOnDelete();
            $table->string('signer_name', 255);
            $table->string('agreement_version', 50)->default('NDFG_OA_v3.1');
            $table->boolean('governance_clause_accepted')->default(true);
            $table->string('ip_address', 45);
            $table->text('browser_user_agent');
            $table->timestamp('signed_timestamp')->useCurrent();
            
            $table->index('investor_id');
        });

        // 6. Investor Audit Logs Table (Full audit trail of all investor and admin actions)
        Schema::create('investor_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_id')->nullable()->constrained('investor_profiles')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50); // 'REGISTERED', 'LOGGED_IN', 'DOC_UPLOADED', 'DOC_DOWNLOADED', 'AGREEMENT_SIGNED', 'STATUS_CHANGED', 'PAYMENT_UNLOCKED', 'EMAIL_SENT', 'NOTE_ADDED'
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('investor_id');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_audit_logs');
        Schema::dropIfExists('esignature_logs');
        Schema::dropIfExists('compliance_vault');
        Schema::dropIfExists('investor_profiles');
        Schema::dropIfExists('country_compliance_rules');
        Schema::dropIfExists('investment_plans');
    }
};
