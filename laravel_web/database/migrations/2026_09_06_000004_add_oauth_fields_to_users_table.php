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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->index()->after('phone_verified_at');
            }
            if (!Schema::hasColumn('users', 'apple_id')) {
                $table->string('apple_id')->nullable()->index()->after('google_id');
            }
            if (!Schema::hasColumn('users', 'oauth_provider')) {
                $table->string('oauth_provider')->nullable()->after('apple_id');
            }
            if (!Schema::hasColumn('users', 'oauth_avatar')) {
                $table->text('oauth_avatar')->nullable()->after('oauth_provider');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('users', 'google_id')) {
                $columnsToDrop[] = 'google_id';
            }
            if (Schema::hasColumn('users', 'apple_id')) {
                $columnsToDrop[] = 'apple_id';
            }
            if (Schema::hasColumn('users', 'oauth_provider')) {
                $columnsToDrop[] = 'oauth_provider';
            }
            if (Schema::hasColumn('users', 'oauth_avatar')) {
                $columnsToDrop[] = 'oauth_avatar';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
