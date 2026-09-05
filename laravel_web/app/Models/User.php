<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'phone', 'phone_verified_at', 'password', 'role', 'avatar', 'referral_code', 'referred_by', 'referrer_id', 'membership_type', 'membership_status', 'membership_price', 'corporate_company_name', 'corporate_billing_email', 'terms_accepted', 'terms_accepted_at', 'terms_version', 'account_status', 'suspension_reason', 'suspended_at', 'admin_notes'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $appends = ['avatar_url'];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = static::generateUniqueReferralCode();
            }
        });
    }

    public static function generateUniqueReferralCode(): string
    {
        do {
            $code = 'RMC' . strtoupper(\Illuminate\Support\Str::random(6));
        } while (static::where('referral_code', $code)->exists());
        return $code;
    }

    public function getReferralCodeAttribute($value): string
    {
        if (empty($value)) {
            $newCode = static::generateUniqueReferralCode();
            $this->attributes['referral_code'] = $newCode;
            if ($this->exists) {
                $this->saveQuietly();
            }
            return $newCode;
        }
        return $value;
    }

    public function getAvatarUrlAttribute(): string
    {
        if (!empty($this->avatar)) {
            return str_starts_with($this->avatar, 'http') ? $this->avatar : asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=f9c52a&color=102b54&bold=true';
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->account_status === 'suspended' || $this->account_status === 'deactivated') {
            return false;
        }

        if (app()->environment('local')) {
            return true;
        }

        return $this->role === 'admin' || $this->email === 'admin@ridemycars.com';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'terms_accepted' => 'boolean',
            'terms_accepted_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    public function driverProfile()
    {
        return $this->hasOne(DriverProfile::class);
    }

    public function driverBookingsAsClient()
    {
        return $this->hasMany(DriverBooking::class, 'client_id');
    }

    public function driverBookingsAsDriver()
    {
        return $this->hasMany(DriverBooking::class, 'driver_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }

    public function privacyRequests()
    {
        return $this->hasMany(PrivacyRequest::class);
    }

    public function savedLocations()
    {
        return $this->hasMany(UserSavedLocation::class);
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class)->orderBy('is_default', 'desc')->latest();
    }

    public function defaultPaymentMethod()
    {
        return $this->hasOne(PaymentMethod::class)->where('is_default', true);
    }
}

