<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_bookings', 'end_date')) {
                $table->date('end_date')->nullable();
            }
            if (!Schema::hasColumn('driver_bookings', 'end_time')) {
                $table->string('end_time')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('driver_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('driver_bookings', 'end_date')) {
                $table->dropColumn('end_date');
            }
            if (Schema::hasColumn('driver_bookings', 'end_time')) {
                $table->dropColumn('end_time');
            }
        });
    }
};
