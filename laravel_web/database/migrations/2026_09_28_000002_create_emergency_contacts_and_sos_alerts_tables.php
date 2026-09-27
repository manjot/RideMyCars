<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Emergency Contacts table
        if (!Schema::hasTable('emergency_contacts')) {
            Schema::create('emergency_contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('name');
                $table->string('phone');
                $table->string('relationship')->default('Contact'); // Parent, Spouse, Sibling, Friend, Colleague, Other
                $table->boolean('is_primary')->default(false);
                $table->boolean('notify_sms')->default(true);
                $table->boolean('notify_whatsapp')->default(true);
                $table->timestamps();

                $table->index(['user_id', 'is_primary']);
            });
        }

        // 2. SOS Alerts Log table
        if (!Schema::hasTable('sos_alerts')) {
            Schema::create('sos_alerts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('ride_id')->nullable()->constrained('rides')->onDelete('set null');
                $table->string('role')->default('rider'); // rider, driver
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('location_address')->nullable();
                $table->string('status')->default('active'); // active, acknowledged, resolved, false_alarm
                $table->integer('contacts_notified_count')->default(0);
                $table->string('emergency_service_dialed')->nullable(); // 911, 112, 100, +18007433692
                $table->text('notes')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sos_alerts');
        Schema::dropIfExists('emergency_contacts');
    }
};
