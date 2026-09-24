<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'booking_type',
        'booking_id',
        'user_id',
        'driver_id',
        'currency',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'fee_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'pdf_path',
        'verification_token',
        'sent_to_email',
        'emailed_at',
        'email_status',
        'snapshot_data',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'tax_amount' => 'float',
        'fee_amount' => 'float',
        'total_amount' => 'float',
        'emailed_at' => 'datetime',
        'snapshot_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function ride()
    {
        return $this->belongsTo(Ride::class, 'booking_id');
    }

    public function driverBooking()
    {
        return $this->belongsTo(DriverBooking::class, 'booking_id');
    }

    public function packageDelivery()
    {
        return $this->belongsTo(PackageDelivery::class, 'booking_id');
    }

    /**
     * Resolve the underlying booking model.
     */
    public function getBookingAttribute()
    {
        return match ($this->booking_type) {
            'ride', 'rental' => Ride::find($this->booking_id),
            'driver_booking' => DriverBooking::find($this->booking_id),
            'delivery' => PackageDelivery::find($this->booking_id),
            default => null,
        };
    }

    /**
     * Get the human-readable booking type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->booking_type) {
            'ride' => 'Ride Hailing',
            'rental' => 'Vehicle Rental',
            'driver_booking' => 'Driver Booking (Chauffeur)',
            'delivery' => 'Package Delivery',
            default => ucfirst(str_replace('_', ' ', $this->booking_type)),
        };
    }

    /**
     * Get the public view URL for this receipt.
     */
    public function getViewUrlAttribute(): string
    {
        return url('/receipts/' . $this->verification_token);
    }

    /**
     * Get the public download URL for this receipt.
     */
    public function getDownloadUrlAttribute(): string
    {
        return url('/receipts/' . $this->verification_token . '/download');
    }

    /**
     * Check if the PDF file exists on storage or public disk.
     */
    public function hasPdf(): bool
    {
        if (empty($this->pdf_path)) {
            return false;
        }

        if (file_exists(public_path($this->pdf_path))) {
            return true;
        }

        if (Storage::disk('public')->exists($this->pdf_path)) {
            return true;
        }

        return false;
    }

    /**
     * Get full absolute path of the PDF file on the server.
     */
    public function getPdfAbsolutePath(): ?string
    {
        if (empty($this->pdf_path)) {
            return null;
        }

        $publicPath = public_path($this->pdf_path);
        if (file_exists($publicPath)) {
            return $publicPath;
        }

        if (Storage::disk('public')->exists($this->pdf_path)) {
            return Storage::disk('public')->path($this->pdf_path);
        }

        return $publicPath;
    }
}
