<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            if (!Schema::hasColumn('rides', 'is_for_someone_else')) {
                $table->boolean('is_for_someone_else')->default(false)->after('payment_method');
            }
            if (!Schema::hasColumn('rides', 'passenger_name')) {
                $table->string('passenger_name')->nullable()->after('is_for_someone_else');
            }
            if (!Schema::hasColumn('rides', 'passenger_phone')) {
                $table->string('passenger_phone')->nullable()->after('passenger_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn(['is_for_someone_else', 'passenger_name', 'passenger_phone']);
        });
    }
};
