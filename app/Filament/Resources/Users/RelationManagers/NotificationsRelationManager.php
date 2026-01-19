<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class NotificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'notifications';

    protected static ?string $title = 'Bildirishnomalar';

    protected static ?string $modelLabel = 'bildirishnoma';

    protected static ?string $pluralModelLabel = 'bildirishnomalar';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Sarlavha')
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('body')
                    ->label('Xabar matni')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Sarlavha')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->color(fn ($record) => !$record->is_read ? 'primary' : 'gray'),

                TextColumn::make('type')
                    ->label('Turi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manual' => 'success',
                        'system' => 'info',
                        default => 'gray',
                    }),

                IconColumn::make('is_read')
                    ->label('O\'qilgan')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Yuborilgan vaqt')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->since()
                    ->description(fn ($record) => $record->created_at->format('d.m.Y H:i')),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Yangi bildirishnoma')
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['type'] = 'manual';
                        $data['is_read'] = false;
                        return $data;
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Bildirishnoma yuborildi')
                            ->body('Yangi bildirishnoma muvaffaqiyatli yaratildi.')
                    ),
            ])
            ->recordActions([
                Action::make('mark_as_read')
                    ->label('O\'qilgan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => !$record->is_read)
                    ->action(function ($record) {
                        $record->update(['is_read' => true]);
                        
                        Notification::make()
                            ->success()
                            ->title('O\'qilgan deb belgilandi')
                            ->send();
                    }),

                Action::make('mark_as_unread')
                    ->label('O\'qilmagan')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn ($record) => $record->is_read)
                    ->action(function ($record) {
                        $record->update(['is_read' => false]);
                        
                        Notification::make()
                            ->success()
                            ->title('O\'qilmagan deb belgilandi')
                            ->send();
                    }),
                DeleteAction::make()
                    ->label('O\'chirish'),
            ])
            ->toolbarActions([
                //
            ])
            ->emptyStateHeading('Bildirishnomalar yo\'q')
            ->emptyStateDescription('Foydalanuvchiga hali bildirishnomalar yuborilmagan.')
            ->emptyStateIcon('heroicon-o-bell-slash');
    }
}