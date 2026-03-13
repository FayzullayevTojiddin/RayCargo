<?php

namespace App\Filament\Resources\CourierProfiles\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
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
                                FileUpload::make('user.image')
                                    ->label('Courier rasmi')
                                    ->image()
                                    ->directory('users'),

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

                                Toggle::make('is_pedestrian')
                                    ->label('Piyoda')
                                    ->reactive(),

                                Select::make('application_status')
                                    ->label('Ariza holati')
                                    ->options([
                                        'pending' => 'Kutilmoqda',
                                        'approved' => 'Tasdiqlangan',
                                        'rejected' => 'Rad etilgan',
                                    ])
                                    ->disabled(),
                            ])
                            ->columnSpan(2),

                        Section::make("Transport ma'lumotlari")
                            ->description("Transport hujjatlari")
                            ->schema([
                                TextInput::make('vehicle_brand')
                                    ->label('Mashina markasi')
                                    ->placeholder('Chevrolet'),

                                TextInput::make('vehicle_model')
                                    ->label('Mashina rusumi')
                                    ->placeholder('Cobalt'),

                                TextInput::make('vehicle_color')
                                    ->label('Mashina rangi')
                                    ->placeholder('Oq'),

                                TextInput::make('vehicle_number')
                                    ->label('Avtomobil raqami')
                                    ->placeholder('01 A 123 BC'),

                                Select::make('vehicle_type_id')
                                    ->label('Transport turi')
                                    ->relationship('vehicleType', 'title')
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('license_number')
                                    ->label('Guvohnoma raqami'),
                            ])
                            ->columnSpan(2)
                            ->hidden(fn ($get) => $get('is_pedestrian')),
                    ])
                    ->columnSpanFull(),

                Section::make('Rad etish sababi')
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Izoh')
                            ->disabled(),
                    ])
                    ->visible(fn ($get) => $get('application_status') === 'rejected')
                    ->columnSpanFull(),
            ]);
    }
}
