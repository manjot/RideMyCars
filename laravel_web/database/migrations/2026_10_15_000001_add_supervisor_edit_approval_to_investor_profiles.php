<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investor_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('investor_profiles', 'edit_approval_status')) {
                $table->string('edit_approval_status', 30)->default('NONE')->after('verification_status');
            }
            if (!Schema::hasColumn('investor_profiles', 'edit_approval_requested_by')) {
                $table->foreignId('edit_approval_requested_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('investor_profiles', 'edit_approval_reason')) {
                $table->text('edit_approval_reason')->nullable();
            }
            if (!Schema::hasColumn('investor_profiles', 'edit_approved_by')) {
                $table->foreignId('edit_approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('investor_profiles', 'edit_approved_at')) {
                $table->timestamp('edit_approved_at')->nullable();
            }
            if (!Schema::hasColumn('investor_profiles', 'edit_approval_supervisor_notes')) {
                $table->text('edit_approval_supervisor_notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('investor_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('investor_profiles', 'edit_approval_requested_by')) {
                $table->dropConstrainedForeignId('edit_approval_requested_by');
            }
            if (Schema::hasColumn('investor_profiles', 'edit_approved_by')) {
                $table->dropConstrainedForeignId('edit_approved_by');
            }
            $columns = [];
            foreach (['edit_approval_status', 'edit_approval_reason', 'edit_approved_at', 'edit_approval_supervisor_notes'] as $col) {
                if (Schema::hasColumn('investor_profiles', $col)) {
                    $columns[] = $col;
                }
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
