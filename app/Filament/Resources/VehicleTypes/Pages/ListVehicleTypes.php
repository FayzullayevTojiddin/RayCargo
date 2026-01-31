<?php

namespace App\Filament\Resources\VehicleTypes\Pages;

use App\Filament\Resources\VehicleTypes\VehicleTypeResource;
use App\Livewire\VehicleType\VehicleTypesStat;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicleTypes extends ListRecords
{
    protected static string $resource = VehicleTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            VehicleTypesStat::class
        ];
    }
}
