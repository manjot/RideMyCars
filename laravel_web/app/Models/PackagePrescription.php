<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackagePrescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_delivery_id',
        'temp_token',
        'file_path',
        'file_name',
        'file_type',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'package_delivery_id' => 'integer',
        'uploaded_by' => 'integer',
    ];

    protected $appends = [
        'view_url',
        'download_url',
        'formatted_size',
        'is_pdf',
        'is_image',
    ];

    public function packageDelivery()
    {
        return $this->belongsTo(PackageDelivery::class, 'package_delivery_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getViewUrlAttribute(): string
    {
        return url('/api/package-delivery/prescriptions/' . $this->id . '/view' . ($this->temp_token ? '?token=' . $this->temp_token : ''));
    }

    public function getDownloadUrlAttribute(): string
    {
        return url('/api/package-delivery/prescriptions/' . $this->id . '/download' . ($this->temp_token ? '?token=' . $this->temp_token : ''));
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    public function getIsPdfAttribute(): bool
    {
        return strtolower($this->file_type ?? '') === 'pdf' || str_contains(strtolower($this->mime_type ?? ''), 'pdf');
    }

    public function getIsImageAttribute(): bool
    {
        $ext = strtolower($this->file_type ?? '');
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) || str_starts_with(strtolower($this->mime_type ?? ''), 'image/');
    }
}
