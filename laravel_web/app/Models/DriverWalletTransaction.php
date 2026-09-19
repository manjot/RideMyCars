<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DriverWalletTransaction extends Model
{
    use HasFactory;

    protected $table = 'driver_wallet_transactions';

    protected $fillable = [
        'driver_id',
        'incentive_id',
        'transaction_ref',
        'milestone_rides',
        'type',
        'amount',
        'currency',
        'description',
        'status',
        'credited_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'milestone_rides' => 'integer',
        'credited_at' => 'datetime',
    ];

    public static function ensureTableExists(): void
    {
        if (!Schema::hasTable('driver_wallet_transactions')) {
            Schema::create('driver_wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('incentive_id')->nullable()->constrained('incentives')->nullOnDelete();
                $table->string('transaction_ref')->unique();
                $table->integer('milestone_rides')->nullable();
                $table->string('type')->default('incentive_bonus');
                $table->decimal('amount', 10, 2);
                $table->string('currency')->default('₹');
                $table->string('description');
                $table->string('status')->default('credited');
                $table->timestamp('credited_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function incentive(): BelongsTo
    {
        return $this->belongsTo(Incentive::class, 'incentive_id');
    }
}
