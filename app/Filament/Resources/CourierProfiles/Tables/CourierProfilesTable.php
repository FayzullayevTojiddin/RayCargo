<?php

namespace App\Filament\Resources\CourierProfiles\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Enums\FiltersLayout;

class CourierProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->filtersLayout(FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->filtersFormColumns(3)
            ->columns([
                ImageColumn::make('user.image')
                    ->label('Rasm')
                    ->circular(),

                TextColumn::make('user.name')
                    ->label('Ism')
                    ->searchable()
                    ->sortable()
                    ->alignCenter(),

                IconColumn::make('is_pedestrian')
                    ->label('Piyoda')
                    ->boolean()
                    ->trueIcon('heroicon-o-user')
                    ->falseIcon('heroicon-o-truck'),


                TextColumn::make('rating')
                    ->label('Reyting')
                    ->numeric(1)
                    ->sortable()
                    ->alignCenter()
                    ->icon('heroicon-s-star')
                    ->iconColor('warning'),

                TextColumn::make('application_status')
                    ->label('Holati')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'Kutilmoqda',
                        'approved' => 'Faol',
                        'rejected' => 'Rad etilgan',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('user.last_seen_at')
                    ->label('So\'nggi faollik')
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

                SelectFilter::make('application_status')
                    ->label('Ariza holati')
                    ->options([
                        'pending' => 'Kutilmoqda',
                        'approved' => 'Tasdiqlangan',
                        'rejected' => 'Rad etilgan',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Arizani tasdiqlash')
                    ->modalDescription('Ushbu courierni tasdiqlashni xohlaysizmi?')
                    ->visible(fn ($record) => $record->application_status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'application_status' => 'approved',
                            'is_active' => true,
                            'rejection_reason' => null,
                        ]);
                    })
                    ->iconButton(),

                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rad etish sababi')
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->visible(fn ($record) => $record->application_status === 'pending')
                    ->action(function ($record, array $data) {
                        $record->update([
                            'application_status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                    })
                    ->iconButton(),

                EditAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
