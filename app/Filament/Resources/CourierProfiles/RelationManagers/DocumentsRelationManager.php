<?php

namespace App\Filament\Resources\CourierProfiles\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $label = 'Hujjatlar';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Hujjatlar va rasmlar';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Turi')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'vehicle_front' => 'Mashina (old)',
                        'vehicle_back' => 'Mashina (orqa)',
                        'vehicle_left' => 'Mashina (chap)',
                        'vehicle_right' => 'Mashina (o\'ng)',
                        'license_front' => 'Guvohnoma (old)',
                        'license_back' => 'Guvohnoma (orqa)',
                        'passport' => 'Passport',
                        default => $state,
                    }),

                ImageColumn::make('file_path')
                    ->label('Rasm')
                    ->disk('public')
                    ->height(80),

                TextColumn::make('status')
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
                        'approved' => 'Tasdiqlangan',
                        'rejected' => 'Rad etilgan',
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Yuklangan')
                    ->dateTime('d.m.Y H:i'),
            ]);
    }
}
