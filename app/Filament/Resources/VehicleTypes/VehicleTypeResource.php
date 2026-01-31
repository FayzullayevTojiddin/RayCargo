<?php

namespace App\Filament\Resources\VehicleTypes;

use App\Filament\Resources\VehicleTypes\Pages\CreateVehicleType;
use App\Filament\Resources\VehicleTypes\Pages\EditVehicleType;
use App\Filament\Resources\VehicleTypes\Pages\ListVehicleTypes;
use App\Filament\Resources\VehicleTypes\RelationManagers\CouriersRelationManager;
use App\Filament\Resources\VehicleTypes\Schemas\VehicleTypeForm;
use App\Filament\Resources\VehicleTypes\Tables\VehicleTypesTable;
use App\Models\VehicleType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VehicleTypeResource extends Resource
{
    protected static ?string $model = VehicleType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::WrenchScrewdriver;

    protected static string | UnitEnum | null $navigationGroup = "Operatsiyalar";

    protected static ?string $navigationLabel = 'Transport turlari';

    protected static ?string $pluralModelLabel = 'Transport turlari';

    protected static ?string $modelLabel = 'transport turi';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return VehicleTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VehicleTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CouriersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVehicleTypes::route('/'),
            'create' => CreateVehicleType::route('/create'),
            'edit' => EditVehicleType::route('/{record}/edit'),
        ];
    }
}
