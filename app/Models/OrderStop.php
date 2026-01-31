<?php

namespace App\Models;

use App\Enums\Order\OrderStopType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStop extends Model
{
    protected $fillable = [
        'order_id',
        'type',
        'sequence',
        'lat',
        'lng',
        'address_text',
        'arrived_at',
        'completed_at',
    ];

    protected $casts = [
        'type' => OrderStopType::class,
        'lat' => 'float',
        'lng' => 'float',
        'arrived_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}