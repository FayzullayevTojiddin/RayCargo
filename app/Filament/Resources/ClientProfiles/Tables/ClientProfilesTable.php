<?php

namespace App\Filament\Resources\ClientProfiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClientProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_online')
                    ->label('Online')
                    ->boolean()
                    ->trueIcon('heroicon-o-signal')
                    ->falseIcon('heroicon-o-signal-slash'),

                IconColumn::make('is_active')
                    ->label('Faol')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                TextColumn::make('last_seen_at')
                    ->label('Oxirgi kirish')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_online')
                    ->label('Online holati')
                    ->options([
                        true => 'Online',
                        false => 'Offline',
                    ]),

                SelectFilter::make('is_active')
                    ->label('Faollik')
                    ->options([
                        1 => 'Faol',
                        0 => 'Faol emas',
                    ]),

                SelectFilter::make('is_online')
                    ->label('Online holati')
                    ->options([
                        1 => 'Online',
                        0 => 'Offline',
                    ])
                    ->native(false)
                    ->default(0),

                SelectFilter::make('is_active')
                    ->label('Faollik')
                    ->options([
                        1 => 'Faol',
                        0 => 'Faol emas',
                    ])
                    ->native(false)
                    ->default(1),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->filtersFormColumns(2)
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