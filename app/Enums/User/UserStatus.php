<?php

namespace App\Enums\User;

enum UserStatus: string
{
    case ACTIVE   = 'active';
    case INACTIVE = 'inactive';
    case BLOCKED  = 'blocked';

    public function label(): string
    {
        return __('index.statuses.' . $this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($status) => [
                $status->value => $status->label(),
            ])
            ->toArray();
    }
}