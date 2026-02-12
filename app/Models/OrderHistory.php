<?php

namespace App\Models;

use App\Enums\Order\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderHistory extends Model
{
    protected $fillable = [
        'order_id',
        'status',
        'previous_status',
        'changed_by',
        'meta',
        'comment',
    ];

    protected $casts = [
        'meta' => 'array',
        'status' => OrderStatus::class,
        'previous_status' => OrderStatus::class
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
