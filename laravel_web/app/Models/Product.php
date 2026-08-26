<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'unit',
        'image',
        'status',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    protected $appends = [
        'formatted_price_with_unit',
    ];

    public static array $unitOptions = [
        'pc' => 'Piece (pc)',
        'kg' => 'Kilogram (kg)',
        'g' => 'Gram (g)',
        'L' => 'Liter (L)',
        'ml' => 'Milliliter (ml)',
        'box' => 'Box',
        'pack' => 'Pack',
        'dozen' => 'Dozen',
        'bottle' => 'Bottle',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getFormattedPriceWithUnitAttribute(): string
    {
        $unitLabel = static::$unitOptions[$this->unit] ?? $this->unit;
        return '$' . number_format($this->price, 2) . ' / ' . $this->unit;
    }
}
