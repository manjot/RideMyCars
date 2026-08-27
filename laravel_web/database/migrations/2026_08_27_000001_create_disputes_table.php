<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('disputes')) {
            Schema::create('disputes', function (Blueprint $table) {
                $table->id();
                $table->string('dispute_code')->unique();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('service_type')->default('rides'); // rides, car_rental, driver, delivery
                $table->string('booking_reference')->nullable();
                $table->string('category')->default('other'); // billing, cancellation, service_quality, safety, property_damage, other
                $table->text('description');
                $table->string('evidence_photo_url')->nullable();
                $table->string('contact_email');
                $table->string('contact_phone')->nullable();
                $table->string('status')->default('open'); // open, under_review, awaiting_customer, resolved, rejected
                $table->boolean('is_within_72h')->default(true);
                $table->timestamp('event_completed_at')->nullable();
                $table->timestamp('deadline_at')->nullable();
                $table->text('admin_notes')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
