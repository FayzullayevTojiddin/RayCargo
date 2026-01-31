<?php

namespace App\Filament\Resources\CourierProfiles\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use App\Enums\User\UserRole;
use Filament\Schemas\Components\Section;

class CourierProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Courier maʼlumotlari')
                    ->columns(4)
                    ->schema([
                        Section::make('Courier ma`lumotlari')
                            ->description("Courier ma'lumotlari")
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Courier rasmi')
                                    ->image()
                                    ->directory('couriers'),

                                Select::make('user_id')
                                    ->label('Courier (User)')
                                    ->relationship(
                                        'user',
                                        'email',
                                        fn ($query) => $query->where('role', UserRole::COURIER)
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])
                            ->columnSpan(2),

                        Section::make("Transport ma'lumotlari")
                            ->description("Transport hujjatlari")
                            ->schema([
                                Select::make('vehicle_type_id')
                                    ->label('Transport turi')
                                    ->relationship('vehicleType', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('vehicle_number')
                                    ->label('Avtomobil raqami')
                                    ->placeholder('01 A 123 BC')
                                    ->required(),

                                TextInput::make('license_number')
                                    ->label('Guvohnoma raqami')
                                    ->required(),
                            ])
                            ->columnSpan(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}