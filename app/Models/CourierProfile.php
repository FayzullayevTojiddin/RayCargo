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
        'is_pedestrian',
        'vehicle_type_id',
        'vehicle_number',
        'vehicle_brand',
        'vehicle_model',
        'vehicle_color',
        'license_number',
        'rating',
        'is_online',
        'is_active',
        'application_status',
        'rejection_reason',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'is_online' => 'boolean',
        'is_active' => 'boolean',
        'is_pedestrian' => 'boolean',
    ];

    public function getLastSeenAtAttribute()
    {
        return $this->user?->last_seen_at;
    }

    public function getImageAttribute()
    {
        return $this->user?->image;
    }

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

    public function documents(): HasMany
    {
        return $this->hasMany(CourierDocument::class);
    }

    public function vehiclePhotos(): HasMany
    {
        return $this->hasMany(CourierDocument::class)->whereIn('type', [
            'vehicle_front', 'vehicle_back', 'vehicle_left', 'vehicle_right',
        ]);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'courier_id', 'user_id');
    }
}