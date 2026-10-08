<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_delivery_id')->nullable()->constrained('package_deliveries')->nullOnDelete();
            $table->string('temp_token', 64)->nullable()->index();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type', 50)->default('jpg'); // jpg, jpeg, png, pdf
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        if (!Schema::hasColumn('package_deliveries', 'has_prescription')) {
            Schema::table('package_deliveries', function (Blueprint $table) {
                $table->boolean('has_prescription')->default(false)->after('package_category');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('package_prescriptions');
        if (Schema::hasColumn('package_deliveries', 'has_prescription')) {
            Schema::table('package_deliveries', function (Blueprint $table) {
                $table->dropColumn('has_prescription');
            });
        }
    }
};
