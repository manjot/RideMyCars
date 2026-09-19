<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DriverIncentiveProgress extends Model
{
    use HasFactory;

    protected $table = 'driver_incentive_progress';

    protected $fillable = [
        'incentive_id',
        'driver_id',
        'period_key',
        'completed_rides_count',
        'achieved_milestones',
        'total_reward_earned',
        'status',
        'last_ride_id',
    ];

    protected $casts = [
        'completed_rides_count' => 'integer',
        'achieved_milestones' => 'array',
        'total_reward_earned' => 'float',
    ];

    public static function ensureTableExists(): void
    {
        if (!Schema::hasTable('driver_incentive_progress')) {
            Schema::create('driver_incentive_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('incentive_id')->constrained('incentives')->onDelete('cascade');
                $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
                $table->string('period_key')->index();
                $table->integer('completed_rides_count')->default(0);
                $table->json('achieved_milestones')->nullable();
                $table->decimal('total_reward_earned', 10, 2)->default(0.00);
                $table->enum('status', ['in_progress', 'completed', 'missed'])->default('in_progress')->index();
                $table->foreignId('last_ride_id')->nullable()->constrained('rides')->nullOnDelete();
                $table->timestamps();

                $table->unique(['incentive_id', 'driver_id', 'period_key'], 'uniq_incentive_driver_period');
            });
        }
    }

    public function incentive(): BelongsTo
    {
        return $this->belongsTo(Incentive::class, 'incentive_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function lastRide(): BelongsTo
    {
        return $this->belongsTo(Ride::class, 'last_ride_id');
    }
}
