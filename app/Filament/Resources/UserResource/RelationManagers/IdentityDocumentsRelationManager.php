<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Support\HtmlString; // Import HtmlString
use Filament\Support\Enums\Icon; // Import Icon for PDF icon

class IdentityDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'identityDocuments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('document_type_id')
                    ->relationship('documentType', 'name')
                    ->required()
                    ->label('Tipo de Documento'),
                TextInput::make('document_number')
                    ->label('Número de Documento')
                    ->maxLength(255),
                SpatieMediaLibraryFileUpload::make('identity_documents')
                    ->collection('identity_documents')
                    ->label('Archivo del Documento')
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->maxSize(5120) // 5MB
                    ->required(),
                // 'status' and 'approved_at' should not be editable directly in the form
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('documentType.name')
            ->columns([
                TextColumn::make('documentType.name')
                    ->label('Tipo de Documento')
                    ->sortable(),
                TextColumn::make('document_number')
                    ->label('Número de Documento')
                    ->searchable(),
                TextColumn::make('file')
                    ->label('Archivo')
                    ->formatStateUsing(function ($state, $record) {
                        dd($record->getFirstMedia('identity_documents')); // <--- CAMBIADO PARA DEPURACIÓN
                        $url = $record->getFirstMediaUrl('identity_documents');
                        if ($url) {
                            $fileName = $record->getFirstMedia('identity_documents')?->file_name ?? 'Ver Archivo';
                            $mimeType = $record->getFirstMedia('identity_documents')?->mime_type;

                            if (str_starts_with($mimeType, 'image/')) {
                                // Larger image preview with Filament's image column classes for lightbox
                                return new HtmlString('<a href="' . $url . '" target="_blank" class="filament-tables-image-column h-24 w-auto object-cover rounded"><img src="' . $url . '" class="h-24 w-auto object-cover rounded" /></a>');
                            } elseif ($mimeType === 'application/pdf') {
                                // PDF icon with link
                                return new HtmlString('<a href="' . $url . '" target="_blank" class="flex items-center space-x-2 text-primary-600 hover:underline">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0L10 5.758 8.621 4.379a3 3 0 00-4.242 0c-1.172 1.172-1.172 3.071 0 4.242L10 14.242l5.621-5.621c1.172-1.172 1.172-3.071 0-4.242zM10 16.5a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                    </svg>
                                    <span>' . $fileName . ' (PDF)</span>
                                </a>');
                            }
                            return new HtmlString('<a href="' . $url . '" target="_blank" class="text-primary-600 hover:underline">' . $fileName . '</a>');
                        }
                        return 'No hay archivo';
                    })
                    ->html()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('approved_at')
                    ->label('Fecha de Aprobación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (\App\Models\IdentityDocument $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Documento aprobado')
                            ->success()
                            ->send();
                    })
                    ->hidden(fn (\App\Models\IdentityDocument $record): bool => $record->status === 'approved'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        return $query;
    }
}
