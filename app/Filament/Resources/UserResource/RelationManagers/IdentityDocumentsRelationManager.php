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
use Illuminate\Support\HtmlString;
use Filament\Support\Enums\Icon;
use App\Models\IdentityDocument; // Importar el modelo IdentityDocument
use Filament\Notifications\Notification; // Importar Notification
use Illuminate\Support\Facades\Log; // Importar Log

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
                    ->label('Archivos del Documento')
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->maxSize(5120) // 5MB
                    ->multiple() // Allow multiple files
                    ->required(),
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
                TextColumn::make('media_files') // Changed column name to reflect multiple files
                ->label('Archivos')
                    ->formatStateUsing(function ($state, $record) {
                        $mediaItems = $record->getMedia('identity_documents');
                        $htmlOutput = '';

                        if ($mediaItems->isEmpty()) {
                            return 'No hay archivos';
                        }

                        foreach ($mediaItems as $media) {
                            $url = $media->getUrl();
                            $fileName = $media->file_name;
                            $mimeType = $media->mime_type;

                            if (str_starts_with($mimeType, 'image/')) {
                                $htmlOutput .= '<a href="' . $url . '" target="_blank" class="filament-tables-image-column h-24 w-auto object-cover rounded" style="margin-right: 8px; margin-bottom: 8px; display: inline-block;"><img src="' . $url . '" class="h-24 w-auto object-cover rounded" /></a>';
                            } elseif ($mimeType === 'application/pdf') {
                                // Simplified PDF link for debugging clickability
                                $htmlOutput .= '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" wire:ignore.self>' . $fileName . ' (PDF)</a>';
                            } else {
                                $htmlOutput .= '<a href="' . $url . '" target="_blank" class="text-primary-600 hover:underline" style="margin-bottom: 8px;">' . $fileName . '</a>';
                            }
                        }
                        return new HtmlString($htmlOutput);
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
                Tables\Actions\CreateAction::make()
                    ->action(function (array $data, \Filament\Forms\Form $form) {
                        $ownerRecord = $this->ownerRecord; // El modelo User asociado al RelationManager
                        $uploadedFiles = $data['identity_documents']; // Obtener los archivos subidos

                        try {
                            IdentityDocument::upsertForUser(
                                $ownerRecord->id,
                                $data['document_type_id'],
                                $data['document_number'],
                                $uploadedFiles
                            );

                            Notification::make()
                                ->title('Documento guardado exitosamente')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al guardar el documento')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->form(function (\App\Models\IdentityDocument $record): array { // Pass $record to the form closure
                        // Asegurar que la relación documentType esté cargada para esta instancia del registro
                        $record->loadMissing('documentType');

                        return [
                            Forms\Components\Group::make()
                                ->schema([
                                    Forms\Components\Section::make('Detalles del Documento')
                                        ->schema([
                                            Forms\Components\Hidden::make('record_id') // Hidden field to pass record ID
                                            ->default($record->id),
                                            TextInput::make('documentType.name')
                                                ->label('Tipo de Documento')
                                                ->formatStateUsing(fn (\App\Models\IdentityDocument $record) => $record->documentType->name ?? 'N/A') // Usar formatStateUsing
                                                ->disabled(),
                                            TextInput::make('status')
                                                ->label('Estado')
                                                ->disabled(),
                                            TextInput::make('approved_at')
                                                ->label('Fecha de Aprobación')
                                                ->disabled(),
                                        ])->columns(2),
                                    Forms\Components\Section::make('Previsualización de Archivos')
                                        ->schema([
                                            SpatieMediaLibraryFileUpload::make('identity_documents')
                                                ->collection('identity_documents')
                                                ->label('Archivos del Documento')
                                                ->multiple()
                                                ->disabled(),
                                            // Nuevo Placeholder para PDFs
                                            Forms\Components\Placeholder::make('pdf_preview')
                                                ->label('Documentos PDF')
                                                ->content(function (\App\Models\IdentityDocument $record) {
                                                    $mediaItems = $record->getMedia('identity_documents')->filter(function ($media) {
                                                        return $media->mime_type === 'application/pdf';
                                                    });

                                                    if ($mediaItems->isEmpty()) {
                                                        return new HtmlString('No hay PDFs adjuntos.');
                                                    }

                                                    $htmlOutput = '<ul>';
                                                    foreach ($mediaItems as $media) {
                                                        $url = $media->getUrl();
                                                        $fileName = $media->file_name;
                                                        $htmlOutput .= '<li><a href="' . $url . '" target="_blank" rel="noopener noreferrer" class="text-primary-600 hover:underline">' . $fileName . '</a></li>';
                                                    }
                                                    $htmlOutput .= '</ul>';
                                                    return new HtmlString($htmlOutput);
                                                })
                                                ->visible(function (\App\Models\IdentityDocument $record) {
                                                    return $record->getMedia('identity_documents')->filter(function ($media) {
                                                        return $media->mime_type === 'application/pdf';
                                                    })->isNotEmpty();
                                                }),
                                        ]),
                                ])->columns(1),
                        ];
                    })
                    ->modalActions([ // CORREGIDO: Usando modalActions
                        Tables\Actions\Action::make('approve_from_view')
                            ->label('Aprobar Documento')
                            ->icon('heroicon-o-check-circle')
                            ->color('success')
                            ->requiresConfirmation()
                            ->action(function (\App\Models\IdentityDocument $record) { // Recibe $record directamente
                                Log::debug('Approve Document modal action record', ['record_id' => $record->id]); // Línea de depuración
                                if (!$record) {
                                    Notification::make()
                                        ->title('Error: Documento no encontrado.')
                                        ->danger()
                                        ->send();
                                    return;
                                }
                                $record->update([
                                    'status' => 'approved',
                                    'approved_at' => now(),
                                ]);
                                Notification::make()
                                    ->title('Documento aprobado')
                                    ->success()
                                    ->send();
                            })
                            ->hidden(function (\App\Models\IdentityDocument $record): bool { // Recibe $record directamente
                                return $record && $record->status === 'approved';
                            }),
                        Tables\Actions\Action::make('cancel_view') // Añadir un botón de cancelar explícito
                            ->label('Cerrar')
                            ->color('gray')
                            ->action(function (Tables\Actions\Action $action) {
                                $action->cancel(); // Cierra el modal
                            }),
                    ]),
                Tables\Actions\DeleteAction::make(),
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
        $query->with(['media', 'documentType']); // Cargar la relación 'media' y 'documentType' para cada documento
        return $query;
    }
}
