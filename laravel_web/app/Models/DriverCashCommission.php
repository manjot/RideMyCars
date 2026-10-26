<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DriverCashCommission extends Model
{
    use HasFactory;

    protected $table = 'driver_cash_commissions';

    protected $fillable = [
        'driver_id',
        'booking_id',
        'service_type',
        'trip_ref',
        'gross_fare',
        'commission_rate',
        'commission_amount',
        'currency',
        'deducted_from_wallet_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'gross_fare' => 'float',
        'commission_rate' => 'float',
        'commission_amount' => 'float',
        'deducted_from_wallet_at' => 'datetime',
    ];

    public static function ensureTableExists(): void
    {
        if (!Schema::hasTable('driver_cash_commissions')) {
            Schema::create('driver_cash_commissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
                $table->unsignedBigInteger('booking_id')->nullable();
                $table->string('service_type')->default('ride');
                $table->string('trip_ref')->nullable();
                $table->decimal('gross_fare', 12, 2)->default(0.00);
                $table->decimal('commission_rate', 5, 2)->default(10.00);
                $table->decimal('commission_amount', 12, 2)->default(0.00);
                $table->string('currency', 10)->default('₹');
                $table->timestamp('deducted_from_wallet_at')->nullable();
                $table->string('status')->default('deducted');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['driver_id', 'created_at']);
                $table->index('service_type');
                $table->index('status');
            });
        }

        if (Schema::hasTable('driver_profiles')) {
            if (!Schema::hasColumn('driver_profiles', 'cash_commission_wallet')) {
                Schema::table('driver_profiles', function (Blueprint $table) {
                    $table->decimal('cash_commission_wallet', 12, 2)->default(0.00)->nullable();
                });
            }
            if (!Schema::hasColumn('driver_profiles', 'active_chauffeur_online')) {
                Schema::table('driver_profiles', function (Blueprint $table) {
                    $table->boolean('active_chauffeur_online')->default(false)->nullable();
                });
            }
            if (!Schema::hasColumn('driver_profiles', 'chauffeur_mode')) {
                Schema::table('driver_profiles', function (Blueprint $table) {
                    $table->boolean('chauffeur_mode')->default(false)->nullable();
                });
            }
        }
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
