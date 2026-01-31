<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CourierProfile extends Model
{
    protected $fillable = [
        'user_id',
        'vehicle_type_id',
        'image',
        'vehicle_number',
        'license_number',
        'rating',
        'is_online',
        'is_active',
        'last_seen_at'
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'is_online' => 'boolean',
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function location(): HasOne
    {
        return $this->hasOne(CourierLocation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'courier_id');
    }
}