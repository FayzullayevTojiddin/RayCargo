<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\Order\OrderStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('client.email')
                    ->label('Client')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('courier.email')
                    ->label('Courier')
                    ->sortable()
                    ->searchable()
                    ->default('—'),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->colors([
                        'warning' => OrderStatus::IN_PROGRESS,
                        'info' => OrderStatus::ACCEPTED,
                        'primary' => OrderStatus::IN_PROGRESS,
                        'success' => OrderStatus::COMPLETED,
                        'danger' => OrderStatus::CANCELLED,
                    ]),

                TextColumn::make('vehicleType.title')
                    ->label('Vehicle Type')
                    ->sortable(),

                TextColumn::make('total_distance_km')
                    ->label('Distance')
                    ->sortable()
                    ->suffix(' km')
                    ->default('—'),

                TextColumn::make('total_price')
                    ->label('Total Price')
                    ->money('USD')
                    ->sortable()
                    ->default('—'),

                TextColumn::make('stops_count')
                    ->label('Stops')
                    ->counts('stops')
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Price Items')
                    ->counts('items')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(OrderStatus::class)
                    ->multiple(),

                SelectFilter::make('vehicle_type_id')
                    ->label('Vehicle Type')
                    ->relationship('vehicleType', 'title')
                    ->multiple()
                    ->preload(),

                SelectFilter::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'email')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('courier_id')
                    ->label('Courier')
                    ->relationship('courier', 'email')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}