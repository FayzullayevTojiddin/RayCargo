<?php

namespace App\Models;

use App\Enums\Order\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'client_id',
        'courier_id',
        'vehicle_type_id',
        'status',
        'total_distance_km',
        'total_price'
    ];

    protected $casts = [
        'total_distance_km' => 'decimal:2',
        'total_price'       => 'decimal:2',
        'status' => OrderStatus::class
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function stops(): HasMany
    {
        return $this->hasMany(OrderStop::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderPriceItem::class);
    }
}
