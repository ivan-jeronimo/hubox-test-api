<?php

namespace App\Filament\Resources\IdentityDocumentResource\Pages;

use App\Filament\Resources\IdentityDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIdentityDocuments extends ListRecords
{
    protected static string $resource = IdentityDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
