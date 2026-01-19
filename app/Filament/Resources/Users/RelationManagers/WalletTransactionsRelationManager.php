<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WalletTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'walletTransactions';

    protected static ?string $title = 'Tranzaksiyalar';

    protected static ?string $modelLabel = 'tranzaksiya';

    protected static ?string $pluralModelLabel = 'tranzaksiyalar';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Barchasi'),
            
            'increase' => Tab::make('Kirim')
                ->modifyQueryUsing(fn ($query) => $query->where('type', 'increase'))
                ->badge(fn () => $this->getOwnerRecord()->walletTransactions()->where('type', 'increase')->count())
                ->badgeColor('success'),
            
            'decrease' => Tab::make('Chiqim')
                ->modifyQueryUsing(fn ($query) => $query->where('type', 'decrease'))
                ->badge(fn () => $this->getOwnerRecord()->walletTransactions()->where('type', 'decrease')->count())
                ->badgeColor('danger'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Turi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'increase' => 'success',
                        'decrease' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'increase' => 'Kirim',
                        'decrease' => 'Chiqim',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Summa')
                    ->formatStateUsing(fn ($state, $record) => 
                        ($record->type === 'increase' ? '+' : '-') . 
                        number_format($state, 0, ',', ' ') . ' so\'m'
                    )
                    ->sortable()
                    ->weight('bold')
                    ->color(fn ($record) => $record->type === 'increase' ? 'success' : 'danger'),

                TextColumn::make('reason')
                    ->label('Sabab')
                    ->searchable()
                    ->wrap()
                    ->limit(30),

                TextColumn::make('reference_id')
                    ->label('Referens ID')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Nusxa olindi')
                    ->placeholder('-'),

                TextColumn::make('description')
                    ->label('Izoh')
                    ->limit(40)
                    ->wrap()
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Sana')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->since()
                    ->description(fn ($record) => $record->created_at->format('d.m.Y H:i')),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateHeading('Tranzaksiyalar yo\'q')
            ->emptyStateDescription('Foydalanuvchining hali tranzaksiyalari mavjud emas.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public function canCreate(): bool
    {
        return false;
    }

    public function canEdit($record): bool
    {
        return false;
    }

    public function canDelete($record): bool
    {
        return false;
    }

    public function canDeleteAny(): bool
    {
        return false;
    }
}