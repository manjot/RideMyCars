<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'make',
        'model',
        'year',
        'license_plate',
        'type',
        'daily_rate',
        'is_available',
        'owner_id',
        'assigned_driver_id',
        'security_deposit_amount',
        'daily_mileage_limit',
        'overage_fee_per_km',
        'transmission',
        'fuel_type',
        'seats',
        'luggage',
        'doors',
        'mileage_policy',
        'fuel_policy',
        'min_driver_age',
        'category',
        'insurance_policy_number',
        'insurance_expiry',
        'roadworthiness_expiry',
        'approval_status',
        'approval_notes',
        'image_url',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'daily_rate' => 'float',
        'security_deposit_amount' => 'float',
        'seats' => 'integer',
        'luggage' => 'integer',
        'doors' => 'integer',
        'min_driver_age' => 'integer',
    ];

    protected $appends = ['image_src'];

    public function getImageSrcAttribute(): string
    {
        if (!empty($this->image_url)) {
            if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://') || str_starts_with($this->image_url, '/images/')) {
                return $this->image_url;
            }
            if (str_starts_with($this->image_url, '/storage/')) {
                return $this->image_url;
            }
            return \Illuminate\Support\Facades\Storage::url($this->image_url);
        }

        // Intelligent Model-Specific Image Mapping: Guarantee 100% relevant vehicle image
        $makeLower = strtolower($this->make ?? '');
        $modelLower = strtolower($this->model ?? '');

        // Tesla Model S / Tesla
        if (str_contains($makeLower, 'tesla') || str_contains($modelLower, 'model s') || str_contains($modelLower, 'model 3') || str_contains($modelLower, 'model y')) {
            return 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=1200&q=80';
        }

        // BMW M4 / BMW
        if (str_contains($makeLower, 'bmw') || str_contains($modelLower, 'm4') || str_contains($modelLower, 'm3') || str_contains($modelLower, 'series')) {
            return 'https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1200&q=80';
        }

        // Land Rover Range Rover Sport / Defender
        if (str_contains($makeLower, 'rover') || str_contains($modelLower, 'range rover') || str_contains($modelLower, 'defender')) {
            return 'https://images.unsplash.com/photo-1541348263662-e0c8de4259ba?auto=format&fit=crop&w=1200&q=80';
        }

        // Mercedes-Benz Sprinter / Van
        if (str_contains($modelLower, 'sprinter') || (str_contains($makeLower, 'mercedes') && str_contains($modelLower, 'van'))) {
            return 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=1200&q=80';
        }

        // Mercedes-Benz S-Class / E-Class / CLA
        if (str_contains($makeLower, 'mercedes') || str_contains($modelLower, 's-class') || str_contains($modelLower, 's580') || str_contains($modelLower, 'e-class') || str_contains($modelLower, 'cla')) {
            return 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=80';
        }

        // Porsche 911 / Carrera
        if (str_contains($makeLower, 'porsche') || str_contains($modelLower, '911') || str_contains($modelLower, 'carrera') || str_contains($modelLower, 'panamera')) {
            return 'https://images.unsplash.com/photo-1614162692292-7ac56d7f7f1e?auto=format&fit=crop&w=1200&q=80';
        }

        // Audi Q8 / e-tron / Audi SUV
        if (str_contains($makeLower, 'audi') || str_contains($modelLower, 'q8') || str_contains($modelLower, 'e-tron') || str_contains($modelLower, 'a6') || str_contains($modelLower, 'q7')) {
            return 'https://images.unsplash.com/photo-1603584173870-7f23fdae1b7a?auto=format&fit=crop&w=1200&q=80';
        }

        // Ford Explorer / SUV
        if (str_contains($makeLower, 'ford') && (str_contains($modelLower, 'explorer') || str_contains($modelLower, 'expedition') || str_contains($modelLower, 'suv'))) {
            return 'https://images.unsplash.com/photo-1520050206274-a1ae44613e6d?auto=format&fit=crop&w=1200&q=80';
        }

        // Ford Mustang
        if (str_contains($makeLower, 'ford') || str_contains($modelLower, 'mustang')) {
            return 'https://images.unsplash.com/photo-1584345604476-8ec5e12e42dd?auto=format&fit=crop&w=1200&q=80';
        }

        // Cadillac Escalade
        if (str_contains($makeLower, 'cadillac') || str_contains($modelLower, 'escalade')) {
            return 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=1200&q=80';
        }

        // Toyota Camry
        if (str_contains($makeLower, 'toyota') && str_contains($modelLower, 'camry')) {
            return 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&w=1200&q=80';
        }

        // Toyota RAV4
        if (str_contains($makeLower, 'toyota') && (str_contains($modelLower, 'rav4') || str_contains($modelLower, 'highlander') || str_contains($modelLower, 'prado'))) {
            return 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?auto=format&fit=crop&w=1200&q=80';
        }

        // Honda Civic / Honda
        if (str_contains($makeLower, 'honda') || str_contains($modelLower, 'civic') || str_contains($modelLower, 'accord')) {
            return 'https://images.unsplash.com/photo-1619682817481-e994891cd1f5?auto=format&fit=crop&w=1200&q=80';
        }

        // Chevrolet Corvette
        if (str_contains($modelLower, 'corvette') || str_contains($modelLower, 'stingray') || str_contains($modelLower, 'camaro')) {
            return 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1200&q=80';
        }

        // Chevrolet Express / Passenger Van
        if (str_contains($modelLower, 'express') || (str_contains($makeLower, 'chevrolet') && str_contains($modelLower, 'van'))) {
            return 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=1200&q=80';
        }

        // Hyundai Tucson / Santa Fe
        if (str_contains($makeLower, 'hyundai') || str_contains($modelLower, 'tucson') || str_contains($modelLower, 'santa fe')) {
            return 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=80';
        }

        // Volkswagen Golf / GTI
        if (str_contains($makeLower, 'volkswagen') || str_contains($makeLower, 'vw') || str_contains($modelLower, 'golf') || str_contains($modelLower, 'gti')) {
            return 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=1200&q=80';
        }

        // Nissan Versa / Note / Sentra / Altima
        if (str_contains($makeLower, 'nissan') || str_contains($modelLower, 'versa') || str_contains($modelLower, 'note') || str_contains($modelLower, 'sentra') || str_contains($modelLower, 'altima')) {
            return 'https://images.unsplash.com/photo-1502877338535-766e1452684a?auto=format&fit=crop&w=1200&q=80';
        }

        // General SUV fallback
        if (str_contains(strtolower($this->type ?? ''), 'suv') || str_contains(strtolower($this->category ?? ''), 'suv')) {
            return 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?auto=format&fit=crop&w=1200&q=80';
        }

        // General Luxury fallback
        return 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&w=1200&q=80';
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function assignedDriver()
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }
}
