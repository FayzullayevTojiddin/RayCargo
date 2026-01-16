<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleType extends Model
{
    protected $fillable = [
        'title',
        'status',
        'max_weight_kg',
        'base_price',
        'price_per_kg',
        'price_per_km',
        'price_per_hour',
        'is_active',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function couriers(): HasMany
    {
        return $this->hasMany(CourierProfile::class);
    }
}
