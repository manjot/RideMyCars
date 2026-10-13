<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_profiles', 'is_chauffeur')) {
                $table->boolean('is_chauffeur')->default(false)->after('is_available');
            }
            if (!Schema::hasColumn('driver_profiles', 'driver_type')) {
                $table->string('driver_type')->default('standard')->after('is_chauffeur');
            }
            if (!Schema::hasColumn('driver_profiles', 'id_card_type')) {
                $table->string('id_card_type')->nullable()->default('national_id')->after('driver_type');
            }
            if (!Schema::hasColumn('driver_profiles', 'id_card_number')) {
                $table->string('id_card_number')->nullable()->after('id_card_type');
            }
            if (!Schema::hasColumn('driver_profiles', 'id_card_expiry')) {
                $table->date('id_card_expiry')->nullable()->after('id_card_number');
            }
            if (!Schema::hasColumn('driver_profiles', 'id_card_front_image')) {
                $table->string('id_card_front_image')->nullable()->after('id_card_expiry');
            }
            if (!Schema::hasColumn('driver_profiles', 'id_card_back_image')) {
                $table->string('id_card_back_image')->nullable()->after('id_card_front_image');
            }
            if (!Schema::hasColumn('driver_profiles', 'id_card_status')) {
                $table->string('id_card_status')->default('pending')->after('id_card_back_image');
            }
            if (!Schema::hasColumn('driver_profiles', 'id_card_rejection_reason')) {
                $table->text('id_card_rejection_reason')->nullable()->after('id_card_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            $cols = [
                'is_chauffeur',
                'driver_type',
                'id_card_type',
                'id_card_number',
                'id_card_expiry',
                'id_card_front_image',
                'id_card_back_image',
                'id_card_status',
                'id_card_rejection_reason',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('driver_profiles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
