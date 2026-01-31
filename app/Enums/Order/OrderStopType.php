<?php

namespace App\Enums\Order;

enum OrderStopType: string
{
    case PICKUP  = 'pickup';
    case DROPOFF = 'dropoff';
    case RETURN  = 'return';

    public function label(): string
    {
        return __('index.order_stop_types.' . $this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($type) => [
                $type->value => $type->label()
            ])
            ->toArray();
    }
}