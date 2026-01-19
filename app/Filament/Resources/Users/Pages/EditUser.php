<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
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
            DeleteAction::make(),
            ActionGroup::make([
                Action::make('send_notification')
                    ->label('Bildirishnoma yuborish')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->modalHeading('Yangi bildirishnoma yuborish')
                    ->form([
                        TextInput::make('title')
                            ->label('Sarlavha')
                            ->required()
                            ->maxLength(255),

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
                    })
                    ->modalSubmitActionLabel('Yuborish')
                    ->modalCancelActionLabel('Bekor qilish'),
            ]),
        ];
    }
}