<?php

namespace App\Filament\Resources\CourierProfiles\Pages;

use App\Filament\Resources\CourierProfiles\CourierProfileResource;
use App\Livewire\Couriers\CourierStats;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCourierProfiles extends ListRecords
{
    protected static string $resource = CourierProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    
    public function getHeaderWidgets(): array
    {
        return [
            CourierStats::class
        ];
    }
}
