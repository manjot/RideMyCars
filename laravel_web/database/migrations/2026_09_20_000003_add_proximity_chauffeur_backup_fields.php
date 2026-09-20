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
        Schema::table('rides', function (Blueprint $table) {
            if (!Schema::hasColumn('rides', 'backup_chauffeur_enabled')) {
                $table->boolean('backup_chauffeur_enabled')->default(false)->after('payment_status');
            }
            if (!Schema::hasColumn('rides', 'backup_driver_id')) {
                $table->foreignId('backup_driver_id')->nullable()->after('driver_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('rides', 'backup_status')) {
                $table->string('backup_status')->nullable()->after('backup_driver_id'); // waiting, accepted, declined, expired, completed
            }
            if (!Schema::hasColumn('rides', 'driver_assignment_type')) {
                $table->string('driver_assignment_type')->default('primary')->after('backup_status'); // primary, backup
            }
            if (!Schema::hasColumn('rides', 'backup_attempt_count')) {
                $table->integer('backup_attempt_count')->default(0)->after('driver_assignment_type');
            }
            if (!Schema::hasColumn('rides', 'backup_reserved_at')) {
                $table->timestamp('backup_reserved_at')->nullable()->after('backup_attempt_count');
            }
            if (!Schema::hasColumn('rides', 'backup_declined_driver_ids')) {
                $table->json('backup_declined_driver_ids')->nullable()->after('backup_reserved_at');
            }
        });

        Schema::table('driver_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_bookings', 'backup_chauffeur_enabled')) {
                $table->boolean('backup_chauffeur_enabled')->default(false)->after('booking_status');
            }
            if (!Schema::hasColumn('driver_bookings', 'backup_driver_id')) {
                $table->foreignId('backup_driver_id')->nullable()->after('driver_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('driver_bookings', 'backup_status')) {
                $table->string('backup_status')->nullable()->after('backup_driver_id');
            }
            if (!Schema::hasColumn('driver_bookings', 'driver_assignment_type')) {
                $table->string('driver_assignment_type')->default('primary')->after('backup_status');
            }
            if (!Schema::hasColumn('driver_bookings', 'backup_attempt_count')) {
                $table->integer('backup_attempt_count')->default(0)->after('driver_assignment_type');
            }
            if (!Schema::hasColumn('driver_bookings', 'backup_reserved_at')) {
                $table->timestamp('backup_reserved_at')->nullable()->after('backup_attempt_count');
            }
            if (!Schema::hasColumn('driver_bookings', 'backup_declined_driver_ids')) {
                $table->json('backup_declined_driver_ids')->nullable()->after('backup_reserved_at');
            }
        });

        Schema::table('ride_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('ride_assignments', 'assignment_type')) {
                $table->string('assignment_type')->default('primary')->after('status'); // primary, backup
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $columns = [
                'backup_chauffeur_enabled',
                'backup_driver_id',
                'backup_status',
                'driver_assignment_type',
                'backup_attempt_count',
                'backup_reserved_at',
                'backup_declined_driver_ids',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('rides', $column)) {
                    if ($column === 'backup_driver_id') {
                        $table->dropForeign(['backup_driver_id']);
                    }
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('driver_bookings', function (Blueprint $table) {
            $columns = [
                'backup_chauffeur_enabled',
                'backup_driver_id',
                'backup_status',
                'driver_assignment_type',
                'backup_attempt_count',
                'backup_reserved_at',
                'backup_declined_driver_ids',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('driver_bookings', $column)) {
                    if ($column === 'backup_driver_id') {
                        $table->dropForeign(['backup_driver_id']);
                    }
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('ride_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('ride_assignments', 'assignment_type')) {
                $table->dropColumn('assignment_type');
            }
        });
    }
};
