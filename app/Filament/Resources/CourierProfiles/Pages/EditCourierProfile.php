<?php

namespace App\Filament\Resources\CourierProfiles\Pages;

use App\Filament\Resources\CourierProfiles\CourierProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCourierProfile extends EditRecord
{
    protected static string $resource = CourierProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
