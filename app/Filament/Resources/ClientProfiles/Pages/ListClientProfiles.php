<?php

namespace App\Filament\Resources\ClientProfiles\Pages;

use App\Filament\Resources\ClientProfiles\ClientProfileResource;
use App\Livewire\Widgets\Clients\ClientStatWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClientProfiles extends ListRecords
{
    protected static string $resource = ClientProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            ClientStatWidget::class
        ];
    }
}
