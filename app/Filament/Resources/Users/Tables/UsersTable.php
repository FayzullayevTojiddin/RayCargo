<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Ism')
                    ->searchable()
                    ->alignCenter(),

                TextColumn::make('role')
                    ->label('Role')
                    ->formatStateUsing(fn (?UserRole $state) => $state?->label())
                    ->alignCenter(),

                TextColumn::make('phone_number')
                    ->label('Telefon Raqami')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (UserStatus $state) => $state->label())
                    ->color(fn (UserStatus $state) => match ($state) {
                        UserStatus::ACTIVE   => 'success',
                        UserStatus::INACTIVE => 'warning',
                        UserStatus::BLOCKED  => 'danger',
                    })
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('wallet.balance')
                    ->label("Balans")
                    ->money('UZS')
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
