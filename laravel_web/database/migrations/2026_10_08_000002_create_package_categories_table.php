<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('package_categories')) {
            Schema::create('package_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->string('icon')->nullable()->default('📦');
                $table->string('badge_text')->nullable();
                $table->decimal('service_fee_percent', 5, 2)->default(5.00);
                $table->boolean('requires_prescription')->default(false);
                $table->string('prescription_note')->nullable();
                $table->string('default_description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });

            // Seed default package categories
            $defaults = [
                [
                    'name' => 'Documents',
                    'slug' => 'documents',
                    'icon' => '📄',
                    'badge_text' => null,
                    'service_fee_percent' => 5.00,
                    'requires_prescription' => false,
                    'prescription_note' => null,
                    'default_description' => 'Important Legal Contracts & Office Supplies',
                    'is_active' => true,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Clothing',
                    'slug' => 'clothing',
                    'icon' => '👕',
                    'badge_text' => null,
                    'service_fee_percent' => 5.00,
                    'requires_prescription' => false,
                    'prescription_note' => null,
                    'default_description' => 'Apparel, Footwear & Accessories',
                    'is_active' => true,
                    'sort_order' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Electronics',
                    'slug' => 'electronics',
                    'icon' => '💻',
                    'badge_text' => null,
                    'service_fee_percent' => 5.00,
                    'requires_prescription' => false,
                    'prescription_note' => null,
                    'default_description' => 'Laptops, Phones & Gadgets',
                    'is_active' => true,
                    'sort_order' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Household items',
                    'slug' => 'household-items',
                    'icon' => '🛋️',
                    'badge_text' => null,
                    'service_fee_percent' => 5.00,
                    'requires_prescription' => false,
                    'prescription_note' => null,
                    'default_description' => 'Home Décor, Kitchenware & Bedding',
                    'is_active' => true,
                    'sort_order' => 4,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Office supplies',
                    'slug' => 'office-supplies',
                    'icon' => '📎',
                    'badge_text' => null,
                    'service_fee_percent' => 5.00,
                    'requires_prescription' => false,
                    'prescription_note' => null,
                    'default_description' => 'Stationery, Files & Printing Material',
                    'is_active' => true,
                    'sort_order' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Personal belongings',
                    'slug' => 'personal-belongings',
                    'icon' => '🎒',
                    'badge_text' => null,
                    'service_fee_percent' => 5.00,
                    'requires_prescription' => false,
                    'prescription_note' => null,
                    'default_description' => 'Keys, Bags, Wallet & Personal Essentials',
                    'is_active' => true,
                    'sort_order' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Pharmeasy',
                    'slug' => 'pharmeasy',
                    'icon' => '💊',
                    'badge_text' => 'Rx',
                    'service_fee_percent' => 10.00,
                    'requires_prescription' => true,
                    'prescription_note' => 'Doctor Prescription Required',
                    'default_description' => 'Prescription Medicines & Healthcare Supplies',
                    'is_active' => true,
                    'sort_order' => 7,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Other',
                    'slug' => 'other',
                    'icon' => '📦',
                    'badge_text' => null,
                    'service_fee_percent' => 5.00,
                    'requires_prescription' => false,
                    'prescription_note' => null,
                    'default_description' => 'General Parcel & Packaged Goods',
                    'is_active' => true,
                    'sort_order' => 8,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            DB::table('package_categories')->insert($defaults);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_categories');
    }
};
