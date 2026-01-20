<?php

namespace App\Enums\User;

enum UserRole: string
{
    case ADMIN   = 'admin';
    case COURIER = 'courier';
    case CLIENT  = 'client';
    case WORKER = 'worker';

    public function label(): string
    {
        return __('index.roles.' . $this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($role) => [
                $role->value => $role->label()
            ])
            ->toArray();
    }
}
