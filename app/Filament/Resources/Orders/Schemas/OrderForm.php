<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\Order\OrderPriceItemType;
use App\Enums\Order\OrderStatus;
use App\Enums\Order\OrderStopType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Buyurtma ma`lumotlari')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('client_id')
                                    ->label('Mijoz')
                                    ->relationship('client', 'email')
                                    ->disabled(),

                                Select::make('courier_id')
                                    ->label('Haydovchi')
                                    ->relationship('courier', 'email')
                                    ->disabled()
                                    ->placeholder('Not assigned'),

                                Select::make('vehicle_type_id')
                                    ->label('Transport turi')
                                    ->relationship('vehicleType', 'title')
                                    ->disabled(),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options(OrderStatus::class)
                                    ->disabled(),

                                TextInput::make('total_distance_km')
                                    ->label('Total Distance (km)')
                                    ->suffix('km')
                                    ->disabled(),

                                TextInput::make('total_price')
                                    ->label('Umumiy')
                                    ->prefix('$')
                                    ->disabled(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('To`xtash nuqtalari')
                    ->headerActions([
                        //
                    ])
                    ->schema([
                        Repeater::make('stops')
                            ->relationship()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('type')
                                            ->label('Turi')
                                            ->options(OrderStopType::class)
                                            ->disabled(),

                                        TextInput::make('sequence')
                                            ->label('Tartib')
                                            ->disabled(),
                                    ]),

                                TextInput::make('address_text')
                                    ->label('Manzil')
                                    ->columnSpanFull()
                                    ->disabled(),

                                Grid::make(2)
                                    ->schema([
                                        DateTimePicker::make('arrived_at')
                                            ->label('Borilgan vaqt')
                                            ->disabled(),

                                        DateTimePicker::make('completed_at')
                                            ->label('Yakunlangan vaqt')
                                            ->disabled(),
                                    ]),
                            ])
                            ->deletable(false)
                            ->addable(false)
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                ($state['sequence'] ?? '?') . '. ' . ($state['address_text'] ?? 'N/A')
                            )
                            ->columnSpanFull(),
                    ]),

                Section::make('Narxlari')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('type')
                                            ->label('Turi')
                                            ->options(OrderPriceItemType::class)
                                            ->disabled(),

                                        TextInput::make('amount')
                                            ->label('Qiymati')
                                            ->prefix('$')
                                            ->disabled(),
                                    ]),

                                TextInput::make('meta')
                                    ->label('Ma`lumotlar')
                                    ->columnSpanFull()
                                    ->disabled(),
                            ])
                            ->deletable(false)
                            ->addable(false)
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['type']) && isset($state['amount']) 
                                    ? "{$state['type']}: \${$state['amount']}" 
                                    : null
                            )
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}