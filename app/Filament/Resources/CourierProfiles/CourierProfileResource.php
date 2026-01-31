<?php

namespace App\Filament\Resources\CourierProfiles;

use App\Filament\Resources\CourierProfiles\Pages\CreateCourierProfile;
use App\Filament\Resources\CourierProfiles\Pages\EditCourierProfile;
use App\Filament\Resources\CourierProfiles\Pages\ListCourierProfiles;
use App\Filament\Resources\CourierProfiles\Schemas\CourierProfileForm;
use App\Filament\Resources\CourierProfiles\Tables\CourierProfilesTable;
use App\Models\CourierProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CourierProfileResource extends Resource
{
    protected static ?string $model = CourierProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Truck;

    protected static string | UnitEnum | null $navigationGroup = "Tizim";

    protected static ?string $navigationLabel = 'Courierlar';

    protected static ?string $pluralModelLabel = 'Courierlar';

    protected static ?string $modelLabel = 'courier';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CourierProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CourierProfilesTable::configure($table);
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
            'index' => ListCourierProfiles::route('/'),
            'create' => CreateCourierProfile::route('/create'),
            'edit' => EditCourierProfile::route('/{record}/edit'),
        ];
    }
}
