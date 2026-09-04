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
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'approval_status')) {
                $table->string('approval_status')->default('approved')->after('is_available');
            }
            if (!Schema::hasColumn('vehicles', 'approval_notes')) {
                $table->text('approval_notes')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('vehicles', 'insurance_policy_number')) {
                $table->string('insurance_policy_number')->nullable()->after('approval_notes');
            }
            if (!Schema::hasColumn('vehicles', 'insurance_expiry')) {
                $table->date('insurance_expiry')->nullable()->after('insurance_policy_number');
            }
            if (!Schema::hasColumn('vehicles', 'roadworthiness_expiry')) {
                $table->date('roadworthiness_expiry')->nullable()->after('insurance_expiry');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['approval_status', 'approval_notes', 'insurance_policy_number', 'insurance_expiry', 'roadworthiness_expiry'] as $column) {
                if (Schema::hasColumn('vehicles', $column)) {
                    $columnsToDrop[] = $column;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
