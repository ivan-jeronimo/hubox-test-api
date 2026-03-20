<?php

namespace App\Filament\Resources\IdentityDocumentResource\Pages;

use App\Filament\Resources\IdentityDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewIdentityDocument extends ViewRecord
{
    protected static string $resource = IdentityDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
