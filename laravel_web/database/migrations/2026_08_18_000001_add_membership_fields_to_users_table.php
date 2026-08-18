<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'membership_type')) {
                $table->string('membership_type')->default('none');
            }
            if (!Schema::hasColumn('users', 'membership_status')) {
                $table->string('membership_status')->default('inactive');
            }
            if (!Schema::hasColumn('users', 'membership_price')) {
                $table->decimal('membership_price', 10, 2)->default(250.00);
            }
            if (!Schema::hasColumn('users', 'corporate_company_name')) {
                $table->string('corporate_company_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'corporate_billing_email')) {
                $table->string('corporate_billing_email')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'membership_type',
                'membership_status',
                'membership_price',
                'corporate_company_name',
                'corporate_billing_email',
            ]);
        });
    }
};
