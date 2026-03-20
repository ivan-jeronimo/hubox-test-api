<?php

namespace App\Filament\Resources\IdentityDocumentResource\Pages;

use App\Filament\Resources\IdentityDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIdentityDocument extends EditRecord
{
    protected static string $resource = IdentityDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
