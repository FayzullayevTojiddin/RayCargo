<?php

namespace App\Filament\Resources\ClientProfiles;

use App\Filament\Resources\ClientProfiles\Pages\CreateClientProfile;
use App\Filament\Resources\ClientProfiles\Pages\EditClientProfile;
use App\Filament\Resources\ClientProfiles\Pages\ListClientProfiles;
use App\Filament\Resources\ClientProfiles\Schemas\ClientProfileForm;
use App\Filament\Resources\ClientProfiles\Tables\ClientProfilesTable;
use App\Models\ClientProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ClientProfileResource extends Resource
{
    protected static ?string $model = ClientProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static string | UnitEnum | null $navigationGroup = "Tizim";

    protected static ?string $navigationLabel = 'Mijozlar';

    protected static ?string $pluralModelLabel = 'Mijozlar';

    protected static ?string $modelLabel = 'mijoz';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ClientProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientProfilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClientProfiles::route('/'),
            'create' => CreateClientProfile::route('/create'),
            'edit' => EditClientProfile::route('/{record}/edit'),
        ];
    }
}
