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
        Schema::table('package_deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('package_deliveries', 'prohibited_items_acknowledged')) {
                $table->boolean('prohibited_items_acknowledged')->default(true)->after('special_handling');
            }
            if (!Schema::hasColumn('package_deliveries', 'inspection_status')) {
                $table->string('inspection_status')->default('not_inspected')->after('prohibited_items_acknowledged');
            }
            if (!Schema::hasColumn('package_deliveries', 'inspection_notes')) {
                $table->text('inspection_notes')->nullable()->after('inspection_status');
            }
            if (!Schema::hasColumn('package_deliveries', 'inspection_photo_url')) {
                $table->string('inspection_photo_url')->nullable()->after('inspection_notes');
            }
            if (!Schema::hasColumn('package_deliveries', 'cancellation_fee')) {
                $table->decimal('cancellation_fee', 10, 2)->default(0.00)->after('delivered_at');
            }
            if (!Schema::hasColumn('package_deliveries', 'penalty_amount')) {
                $table->decimal('penalty_amount', 10, 2)->default(0.00)->after('cancellation_fee');
            }
            if (!Schema::hasColumn('package_deliveries', 'return_fee')) {
                $table->decimal('return_fee', 10, 2)->default(0.00)->after('penalty_amount');
            }
            if (!Schema::hasColumn('package_deliveries', 'eligible_refund_amount')) {
                $table->decimal('eligible_refund_amount', 10, 2)->default(0.00)->after('return_fee');
            }
            if (!Schema::hasColumn('package_deliveries', 'refund_amount')) {
                $table->decimal('refund_amount', 10, 2)->default(0.00)->after('eligible_refund_amount');
            }
            if (!Schema::hasColumn('package_deliveries', 'refund_status')) {
                $table->string('refund_status')->default('none')->after('refund_amount');
            }
            if (!Schema::hasColumn('package_deliveries', 'refund_reference')) {
                $table->string('refund_reference')->nullable()->after('refund_status');
            }
            if (!Schema::hasColumn('package_deliveries', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('refund_reference');
            }
            if (!Schema::hasColumn('package_deliveries', 'accepted_at')) {
                $table->timestamp('accepted_at')->nullable()->after('refunded_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_deliveries', function (Blueprint $table) {
            $columnsToDrop = [
                'prohibited_items_acknowledged',
                'inspection_status',
                'inspection_notes',
                'inspection_photo_url',
                'cancellation_fee',
                'penalty_amount',
                'return_fee',
                'eligible_refund_amount',
                'refund_amount',
                'refund_status',
                'refund_reference',
                'refunded_at',
                'accepted_at',
            ];

            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('package_deliveries', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
