<?php

namespace App\Filament\Resources\VehicleTypes\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Enums\FiltersLayout;

class VehicleTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Transport turi')
                    ->searchable()
                    ->sortable()
                    ->limit(25),

                TextColumn::make('max_weight_kg')
                    ->label('Max yuk')
                    ->suffix(' kg')
                    ->sortable(),

                TextColumn::make('base_price')
                    ->label('Boshlang‘ich narx')
                    ->money('UZS')
                    ->sortable(),

                TextColumn::make('price_per_km')
                    ->label('1 km narxi')
                    ->money('UZS')
                    ->sortable(),

                TextColumn::make('price_per_hour')
                    ->label('1 soat narxi')
                    ->money('UZS')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Faol'),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Faolligi'),

                TernaryFilter::make('status')
                    ->label('Status'),
            ])
            ->deferFilters(false)
            ->filtersFormColumns(2)
            ->filtersLayout(FiltersLayout::AboveContent)
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}