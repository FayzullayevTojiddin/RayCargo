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
                    ->colors([
                        'warning' => OrderStatus::IN_PROGRESS,
                        'info' => OrderStatus::ACCEPTED,
                        'primary' => OrderStatus::IN_PROGRESS,
                        'success' => OrderStatus::COMPLETED,
                        'danger' => OrderStatus::CANCELLED,
                    ]),

                TextColumn::make('total_distance_km')
                    ->label('Masofa')
                    ->sortable()
                    ->suffix(' km')
                    ->default('—'),

                TextColumn::make('created_at')
                    ->label('Created')
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