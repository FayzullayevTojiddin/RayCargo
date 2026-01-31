<?php

namespace App\Enums\Order;

enum OrderPriceItemType: string
{
    case BASE     = 'base';
    case DELIVERY = 'delivery';
    case SERVICE  = 'service';
    case DISCOUNT = 'discount';
    case BONUS    = 'bonus';
    case TAX      = 'tax';

    public function label(): string
    {
        return __('index.order_price_item_types.' . $this->value);
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