<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\Order\OrderStatus;
use App\Enums\User\UserRole;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Enums\FiltersLayout;
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

                BadgeColumn::make('status')
                    ->label('Holat')
                    ->sortable()
                    ->formatStateUsing(fn (OrderStatus $state) => $state->label())
                    ->color(fn (OrderStatus $state) => $state->color()),

                TextColumn::make('total_distance_km')
                    ->label('Masofa')
                    ->sortable()
                    ->suffix(' km')
                    ->default('—'),

                TextColumn::make('total_price')
                    ->label('Umumiy narx')
                    ->money('UZS'),

                TextColumn::make('created_at')
                    ->label('Yaratilingan vaqti')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Holat')
                    ->options(OrderStatus::options())
                    ->multiple(),

                SelectFilter::make('vehicle_type_id')
                    ->label('Transport turi')
                    ->relationship('vehicleType', 'title')
                    ->multiple()
                    ->preload(),

                SelectFilter::make('courier_id')
                    ->label('Courier')
                    ->relationship('courier', 'email', fn($query) => $query->where('role', UserRole::COURIER))
                    ->preload()
                    ->searchable()
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->filtersFormColumns(3)
            ->recordActions([
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}