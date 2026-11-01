<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TripMessage extends Model
{
    protected $table = 'trip_messages';

    protected $fillable = [
        'trip_type',
        'trip_id',
        'sender_id',
        'sender_role',
        'sender_name',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Ensure the trip_messages table exists in the database.
     */
    public static function ensureTableExists(): void
    {
        try {
            if (!Schema::hasTable('trip_messages')) {
                Schema::create('trip_messages', function (Blueprint $table) {
                    $table->id();
                    $table->string('trip_type', 40)->default('ride')->index();
                    $table->unsignedBigInteger('trip_id')->index();
                    $table->unsignedBigInteger('sender_id')->nullable();
                    $table->string('sender_role', 30)->default('rider');
                    $table->string('sender_name', 120)->nullable();
                    $table->text('message');
                    $table->boolean('is_read')->default(false);
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // Ignore schema creation race conditions
        }
    }
}

