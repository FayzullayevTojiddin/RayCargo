<?php

namespace App\Filament\Resources\VehicleTypes\RelationManagers;

use App\Filament\Resources\CourierProfiles\CourierProfileResource;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CouriersRelationManager extends RelationManager
{
    protected static string $relationship = 'couriers';

    protected static ?string $label = "Courierlar";

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return "Coruierlar";
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user_id')
            ->columns([
                ImageColumn::make('image')
                    ->label('Rasm')
                    ->circular(),

                TextColumn::make('user.email')
                    ->label('Courier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vehicle_number')
                    ->label('Avto raqam')
                    ->searchable(),

                TextColumn::make('rating')
                    ->label('Reyting')
                    ->numeric(1)
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                DeleteAction::make()->button(),
                Action::make('openCourier')
                    ->label('Profil')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => CourierProfileResource::getUrl('edit', [
                        'record' => $record,
                    ]))
                    ->openUrlInNewTab()
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
