<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('notifications')
                ->label(fn ($record) => 'Bildirishnomalar (' . $record->notifications()->where('is_read', false)->count() . ')')
                ->icon('heroicon-o-bell')
                ->modalHeading('Bildirishnomalar')
                ->modalWidth('lg')
                ->form([
                    TextInput::make('title')
                        ->label('Sarlavha')
                        ->required(),

                    Textarea::make('body')
                        ->label('Xabar matni')
                        ->rows(4)
                        ->required(),
                ])
                ->action(function (array $data, $record) {
                    $record->notifications()->create([
                        'type' => 'manual',
                        'title' => $data['title'],
                        'body' => $data['body'],
                        'is_read' => false,
                    ]);

                    Notification::make()
                        ->title('Bildirishnoma yuborildi')
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }
}
