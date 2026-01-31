<?php

namespace App\Models;

use App\Enums\Order\OrderPriceItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPriceItem extends Model
{
    protected $fillable = [
        'order_id',
        'type',
        'amount',
        'meta',
    ];

    protected $casts = [
        'type' => OrderPriceItemType::class,
        'amount' => 'integer',
        'meta' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}