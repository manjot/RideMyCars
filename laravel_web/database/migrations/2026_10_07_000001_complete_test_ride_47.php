<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rides')) {
            DB::table('rides')->where('id', 47)->update(['status' => 'cancelled']);
        }
        if (Schema::hasTable('ride_assignments')) {
            DB::table('ride_assignments')->where('ride_id', 47)->update(['status' => 'cancelled']);
        }
    }

    public function down(): void
    {
    }
};
