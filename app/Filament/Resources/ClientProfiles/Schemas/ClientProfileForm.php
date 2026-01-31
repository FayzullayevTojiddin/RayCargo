<?php

namespace App\Filament\Resources\ClientProfiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mijoz ma`lumotlari')
                    ->description("Mijoz ma'lumotlari")
                    ->schema([
                        Select::make('user_id')
                            ->label('Mijoz')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(2),

                        DateTimePicker::make('last_seen_at')
                            ->label('Oxirgi kirish vaqti')
                            ->seconds(false)
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(2),
                    ])
                    ->columns(4)
                    ->columnSpanFull()
            ]);
    }
}
