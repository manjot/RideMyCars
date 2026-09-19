<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Incentive extends Model
{
    use HasFactory;

    protected $table = 'incentives';

    protected $fillable = [
        'name',
        'description',
        'type',
        'country',
        'state',
        'city',
        'zone',
        'vehicle_type',
        'targets',
        'schedule_type',
        'start_date',
        'end_date',
        'status',
        'notify_on_start',
        'notify_on_reward',
        'currency',
    ];

    protected $casts = [
        'targets' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'notify_on_start' => 'boolean',
        'notify_on_reward' => 'boolean',
    ];

    /**
     * Self-healing table check to guarantee fail-safe runtime operation.
     */
    public static function ensureTableExists(): void
    {
        if (!Schema::hasTable('incentives')) {
            Schema::create('incentives', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->enum('type', ['daily', 'weekly', 'monthly'])->default('daily');
                $table->string('country')->index();
                $table->string('state')->nullable()->index();
                $table->string('city')->nullable()->index();
                $table->string('zone')->nullable()->index();
                $table->string('vehicle_type')->default('All Vehicles')->index();
                $table->json('targets')->nullable();
                $table->string('schedule_type')->default('every_day');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active')->index();
                $table->boolean('notify_on_start')->default(true);
                $table->boolean('notify_on_reward')->default(true);
                $table->string('currency')->default('₹');
                $table->timestamps();
            });
        }
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(DriverIncentiveProgress::class, 'incentive_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(DriverWalletTransaction::class, 'incentive_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDaily($query)
    {
        return $query->where('type', 'daily');
    }

    public function scopeWeekly($query)
    {
        return $query->where('type', 'weekly');
    }

    public function scopeMonthly($query)
    {
        return $query->where('type', 'monthly');
    }

    /**
     * Get sorted milestone targets ascending by rides.
     */
    public function getSortedTargets(): array
    {
        $raw = is_array($this->targets) ? $this->targets : [];
        usort($raw, function ($a, $b) {
            $rA = (int)($a['rides'] ?? 0);
            $rB = (int)($b['rides'] ?? 0);
            return $rA <=> $rB;
        });
        return $raw;
    }

    /**
     * Get the max milestone target (rides count and reward).
     */
    public function getMaxTarget(): array
    {
        $sorted = $this->getSortedTargets();
        return !empty($sorted) ? end($sorted) : ['rides' => 0, 'reward' => 0];
    }

    /**
     * Get the current period key for this incentive based on type.
     */
    public function getPeriodKey(?Carbon $date = null): string
    {
        $d = $date ?: Carbon::now();
        if ($this->type === 'weekly') {
            return $d->format('o-\WW'); // e.g. 2026-W38
        }
        if ($this->type === 'monthly') {
            return $d->format('Y-m'); // e.g. 2026-09
        }
        // daily
        return $d->format('Y-m-d'); // e.g. 2026-09-19
    }

    /**
     * Check if this incentive is currently active for given date.
     */
    public function isScheduleActive(?Carbon $date = null): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $d = ($date ?: Carbon::now())->copy()->startOfDay();

        if ($this->start_date && $d->lt(Carbon::parse($this->start_date)->startOfDay())) {
            return false;
        }

        if ($this->end_date && $d->gt(Carbon::parse($this->end_date)->endOfDay())) {
            return false;
        }

        return true;
    }

    /**
     * Check if this incentive is applicable to a specific driver.
     */
    public function matchesDriver(User $driver): bool
    {
        $profile = $driver->driverProfile;
        
        $driverCountry = trim($profile?->country ?? $driver->country ?? '');
        $driverState = trim($profile?->state ?? '');
        $driverCity = trim($profile?->city ?? $profile?->service_area ?? $driver->city ?? '');
        $driverZone = trim($profile?->zone ?? '');
        $driverVehicle = trim($profile?->vehicle_type ?? '');

        // 1. Country match (Required)
        if (empty($this->country)) {
            return false;
        }

        if (strcasecmp($this->country, $driverCountry) !== 0 &&
            !str_contains(strtolower($driverCountry), strtolower($this->country)) &&
            !str_contains(strtolower($this->country), strtolower($driverCountry))) {
            return false;
        }

        // 2. State match (Optional)
        if (!empty($this->state)) {
            if (empty($driverState) || strcasecmp($this->state, $driverState) !== 0) {
                return false;
            }
        }

        // 3. City match (Optional)
        if (!empty($this->city)) {
            if (empty($driverCity) || (strcasecmp($this->city, $driverCity) !== 0 && !str_contains(strtolower($driverCity), strtolower($this->city)))) {
                return false;
            }
        }

        // 4. Zone match (Optional)
        if (!empty($this->zone)) {
            if (empty($driverZone) || strcasecmp($this->zone, $driverZone) !== 0) {
                return false;
            }
        }

        // 5. Vehicle Type match (Optional / 'All Vehicles')
        $targetVehicle = trim($this->vehicle_type ?? 'All Vehicles');
        if (!empty($targetVehicle) && strcasecmp($targetVehicle, 'All Vehicles') !== 0 && strcasecmp($targetVehicle, 'All') !== 0) {
            if (!empty($driverVehicle) && strcasecmp($targetVehicle, $driverVehicle) !== 0) {
                return false;
            }
        }

        return true;
    }
}
