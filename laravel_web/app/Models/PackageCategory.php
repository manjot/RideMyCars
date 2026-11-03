<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PackageCategory extends Model
{
    use HasFactory;

    protected $table = 'package_categories';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'badge_text',
        'service_fee_percent',
        'requires_prescription',
        'prescription_note',
        'default_description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'service_fee_percent' => 'float',
        'requires_prescription' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted()
    {
        static::saving(function ($category) {
            if (empty($category->slug) && !empty($category->name)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Ensure default categories (including Food) exist in the database once.
     */
    public static function ensureDefaultsSeeded(): void
    {
        try {
            if (!Schema::hasTable('package_categories')) {
                return;
            }

            if (static::count() === 0) {
                foreach (static::rawDefaultCategories() as $item) {
                    $data = $item;
                    unset($data['id']);
                    static::firstOrCreate(
                        ['slug' => $data['slug']],
                        $data
                    );
                }
                \App\Services\SettingService::set('package_categories.food_seeded_v1', '1', 'Delivery');
                return;
            }

            // One-time seed of 'Food' category into existing tables while respecting subsequent Admin CRUD
            $foodSeeded = \App\Services\SettingService::get('package_categories.food_seeded_v1');
            if (!$foodSeeded) {
                $exists = static::whereRaw('LOWER(name) = ?', ['food'])
                    ->orWhereRaw('LOWER(slug) = ?', ['food'])
                    ->exists();
                if (!$exists) {
                    static::where('slug', 'other')->where('sort_order', 8)->update(['sort_order' => 9]);
                    static::create([
                        'name' => 'Food',
                        'slug' => 'food',
                        'icon' => '🍔',
                        'badge_text' => null,
                        'service_fee_percent' => 5.00,
                        'requires_prescription' => false,
                        'prescription_note' => null,
                        'default_description' => 'Prepared Meals, Groceries & Restaurant Orders',
                        'is_active' => true,
                        'sort_order' => 8,
                    ]);
                }
                \App\Services\SettingService::set('package_categories.food_seeded_v1', '1', 'Delivery');
            }
        } catch (\Throwable $e) {
            // Ignore during early boot / before settings table exists
        }
    }

    /**
     * Get active package categories for web & API with fallback to built-in list.
     */
    public static function getActiveCategories()
    {
        try {
            if (Schema::hasTable('package_categories')) {
                static::ensureDefaultsSeeded();

                $categories = static::where('is_active', true)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                if ($categories->isNotEmpty() || static::count() > 0) {
                    return $categories;
                }
            }
        } catch (\Throwable $e) {
            // Table doesn't exist yet or connection issue
        }

        return static::fallbackCollection();
    }

    /**
     * Get service fee percentage for a given category name (case-insensitive).
     */
    public static function getFeePercentFor(?string $categoryName): float
    {
        if (empty($categoryName)) {
            return 5.00;
        }

        $clean = trim($categoryName);

        try {
            if (Schema::hasTable('package_categories')) {
                $cat = static::where('is_active', true)
                    ->where(function ($q) use ($clean) {
                        $q->whereRaw('LOWER(name) = ?', [strtolower($clean)])
                          ->orWhereRaw('LOWER(slug) = ?', [strtolower($clean)]);
                    })
                    ->first();

                if ($cat && isset($cat->service_fee_percent)) {
                    return (float)$cat->service_fee_percent;
                }
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return strtolower($clean) === 'pharmeasy' ? 10.00 : 5.00;
    }

    /**
     * Check if a category requires doctor prescription.
     */
    public static function isPrescriptionMandatory(?string $categoryName): bool
    {
        if (empty($categoryName)) {
            return false;
        }

        $clean = trim($categoryName);

        try {
            if (Schema::hasTable('package_categories')) {
                $cat = static::where('is_active', true)
                    ->where(function ($q) use ($clean) {
                        $q->whereRaw('LOWER(name) = ?', [strtolower($clean)])
                          ->orWhereRaw('LOWER(slug) = ?', [strtolower($clean)]);
                    })
                    ->first();

                if ($cat) {
                    return (bool)$cat->requires_prescription;
                }
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return strtolower($clean) === 'pharmeasy';
    }

    /**
     * Raw default categories array.
     */
    public static function rawDefaultCategories(): array
    {
        return [
            [
                'id' => 1,
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
            ],
            [
                'id' => 2,
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
            ],
            [
                'id' => 3,
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
            ],
            [
                'id' => 4,
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
            ],
            [
                'id' => 5,
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
            ],
            [
                'id' => 6,
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
            ],
            [
                'id' => 7,
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
            ],
            [
                'id' => 8,
                'name' => 'Food',
                'slug' => 'food',
                'icon' => '🍔',
                'badge_text' => null,
                'service_fee_percent' => 5.00,
                'requires_prescription' => false,
                'prescription_note' => null,
                'default_description' => 'Prepared Meals, Groceries & Restaurant Orders',
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'id' => 9,
                'name' => 'Other',
                'slug' => 'other',
                'icon' => '📦',
                'badge_text' => null,
                'service_fee_percent' => 5.00,
                'requires_prescription' => false,
                'prescription_note' => null,
                'default_description' => 'General Parcel & Packaged Goods',
                'is_active' => true,
                'sort_order' => 9,
            ],
        ];
    }

    /**
     * Fallback collection of categories when table hasn't been migrated yet.
     */
    public static function fallbackCollection()
    {
        $raw = static::rawDefaultCategories();

        return collect($raw)->map(function ($item) {
            $cat = new static();
            $cat->fill($item);
            $cat->id = $item['id'];
            $cat->exists = false;
            return $cat;
        });
    }
}
