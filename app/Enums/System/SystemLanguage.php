<?php

namespace App\Enums\System;

enum SystemLanguage: string
{
    case UZ = 'uz';
    case RU = 'ru';
    case EN = 'en';

    public function label(): string
    {
        return match ($this) {
            self::UZ => 'O‘zbekcha',
            self::RU => 'Русский',
            self::EN => 'English',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($lang) => [
                $lang->value => $lang->label(),
            ])
            ->toArray();
    }
}
