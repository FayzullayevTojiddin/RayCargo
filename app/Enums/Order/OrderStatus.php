<?php

namespace App\Enums\Order;

enum OrderStatus: string
{
    case CREATED     = 'created';
    case ACCEPTED    = 'accepted';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED   = 'completed';
    case CANCELLED   = 'cancelled';

    public function label(): string
    {
        return __('index.order_statuses.' . $this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [
                $status->value => $status->label(),
            ])
            ->toArray();
    }
}