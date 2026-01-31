<?php

namespace App\Filament\Resources\CourierProfiles\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Enums\FiltersLayout;

class CourierProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->filtersLayout(FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->filtersFormColumns(2)
            ->columns([
                ImageColumn::make('image')
                    ->label('Rasm')
                    ->circular(),

                TextColumn::make('user.email')
                    ->label('Courier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vehicleType.title')
                    ->label('Transport turi')
                    ->sortable(),

                TextColumn::make('vehicle_number')
                    ->label('Avto raqam')
                    ->searchable(),

                TextColumn::make('rating')
                    ->label('Reyting')
                    ->numeric(1)
                    ->sortable(),

                TextColumn::make('last_seen_at')
                    ->label('Oxirgi ko‘rilgan')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_online')
                    ->label('Online holati')
                    ->trueLabel('Online')
                    ->falseLabel('Offline')
                    ->nullable()
                    ->placeholder('Barchasi'),

                SelectFilter::make('vehicle_type_id')
                    ->label('Transport turi')
                    ->relationship('vehicleType', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}