<?php

namespace App\Filament\Resources\CourierProfiles\RelationManagers;

use App\Enums\Order\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Buyurtmalar';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Holat')
                    ->sortable()
                    ->formatStateUsing(fn (OrderStatus $state) => $state->label())
                    ->color(fn (OrderStatus $state) => $state->color()),

                TextColumn::make('vehicleType.title')
                    ->label('Transport'),

                TextColumn::make('total_distance_km')
                    ->label('Masofa')
                    ->suffix(' km'),

                TextColumn::make('total_price')
                    ->label('Narxi')
                    ->money('UZS'),

                TextColumn::make('created_at')
                    ->label('Sana')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Holat')
                    ->options(OrderStatus::options())
                    ->multiple(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn ($record) => OrderResource::getUrl('edit', ['record' => $record]))
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
