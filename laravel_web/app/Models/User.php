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

#[Fillable(['name', 'email', 'password', 'role', 'membership_type', 'membership_status', 'membership_price', 'corporate_company_name', 'corporate_billing_email', 'terms_accepted', 'terms_accepted_at', 'terms_version', 'account_status', 'suspension_reason', 'suspended_at', 'admin_notes'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    public function canAccessPanel(Panel $panel): bool
    {
        return ($this->role === 'admin' || $this->email === 'admin@ridemycars.com') && $this->account_status !== 'suspended' && $this->account_status !== 'deactivated';
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
}

