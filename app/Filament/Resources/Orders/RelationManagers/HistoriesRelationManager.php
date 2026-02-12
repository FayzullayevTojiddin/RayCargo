<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Enums\Order\OrderStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'histories';

    protected static ?string $title = 'Buyurtma tarixi';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->label('Holat')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state) => $state->label()),
                
                TextColumn::make('previous_status')
                    ->label('Oldingi holat')
                    ->badge()
                    ->formatStateUsing(fn (?OrderStatus $state) => $state?->label() ?? '-'),
                
                TextColumn::make('changedBy.name')
                    ->label("O'zgartirdi")
                    ->default('-'),
                
                TextColumn::make('comment')
                    ->label('Izoh')
                    ->limit(50)
                    ->default('-'),
                
                TextColumn::make('created_at')
                    ->label('Sana')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}