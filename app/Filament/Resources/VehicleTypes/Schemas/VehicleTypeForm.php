<?php

namespace App\Filament\Resources\VehicleTypes\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class VehicleTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transport turi maʼlumotlari')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Transport nomi')
                            ->placeholder('Masalan: Yengil avtomobil, Moto, Yuk mashinasi')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('max_weight_kg')
                            ->label('Maksimal yuk (kg)')
                            ->numeric()
                            ->suffix('kg')
                            ->required()
                            ->columnSpan(2),

                        TextInput::make('base_price')
                            ->label('Boshlang‘ich narx')
                            ->numeric()
                            ->prefix('UZS')
                            ->required(),

                        TextInput::make('price_per_kg')
                            ->label('1 kg uchun narx')
                            ->numeric()
                            ->prefix('UZS'),

                        TextInput::make('price_per_km')
                            ->label('1 km uchun narx')
                            ->numeric()
                            ->prefix('UZS'),

                        TextInput::make('price_per_hour')
                            ->label('1 soat uchun narx')
                            ->numeric()
                            ->prefix('UZS'),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
            ]);
    }
}