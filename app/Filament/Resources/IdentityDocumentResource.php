<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IdentityDocumentResource\Pages;
use App\Filament\Resources\IdentityDocumentResource\RelationManagers;
use App\Models\IdentityDocument;
use App\Models\User;
use App\Models\DocumentType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class IdentityDocumentResource extends Resource
{
    protected static ?string $model = IdentityDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = 'Gestión de Documentos';
    protected static ?string $modelLabel = 'Documento de Identidad';
    protected static ?string $pluralModelLabel = 'Documentos de Identidad';

    // Ocultar este recurso del menú de navegación
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Documento')
                    ->schema([
                        Select::make('user_id')
                            ->label('Usuario')
                            ->relationship('user', 'email') // Muestra el email del usuario
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                        Select::make('document_type_id')
                            ->label('Tipo de Documento')
                            ->relationship('documentType', 'name') // Muestra el nombre del tipo de documento
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('document_number')
                            ->label('Número de Documento')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'approved' => 'Aprobado',
                                'rejected' => 'Rechazado',
                            ])
                            ->default('pending')
                            ->required()
                            ->columnSpan(1),
                        SpatieMediaLibraryFileUpload::make('identity_documents')
                            ->label('Archivo del Documento')
                            ->collection('identity_documents')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(5120) // 5MB
                            ->downloadable()
                            ->openable()
                            ->columnSpan(2),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.full_name') // Asumiendo que tienes el accesor full_name en el modelo User
                    ->label('Usuario')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('documentType.name')
                    ->label('Tipo de Documento')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('document_number')
                    ->label('Número')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->sortable(),
                SpatieMediaLibraryImageColumn::make('identity_documents')
                    ->label('Archivo')
                    ->collection('identity_documents')
                    ->width(50)
                    ->height(50)
                    ->circular()
                    ->toggleable(),
                TextColumn::make('approved_by')
                    ->label('Aprobado Por')
                    ->formatStateUsing(fn (?string $state) => $state ? User::find($state)?->full_name ?? 'N/A' : 'N/A')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('approved_at')
                    ->label('Fecha Aprobación')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('document_type_id')
                    ->label('Tipo de Documento')
                    ->relationship('documentType', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Documento')
                    ->modalDescription('¿Estás seguro de que quieres aprobar este documento? Esta acción no se puede deshacer.')
                    ->action(function (IdentityDocument $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                        ]);
                        Notification::make()
                            ->title('Documento Aprobado')
                            ->success()
                            ->send();
                    })
                    ->hidden(fn (IdentityDocument $record): bool => $record->status === 'approved'), // Ocultar si ya está aprobado
                Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rechazar Documento')
                    ->modalDescription('¿Estás seguro de que quieres rechazar este documento? Esta acción no se puede deshacer.')
                    ->action(function (IdentityDocument $record) {
                        $record->update([
                            'status' => 'rejected',
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                        ]);
                        Notification::make()
                            ->title('Documento Rechazado')
                            ->danger()
                            ->send();
                    })
                    ->hidden(fn (IdentityDocument $record): bool => $record->status !== 'pending'), // Ocultar si no está pendiente
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIdentityDocuments::route('/'),
            'create' => Pages\CreateIdentityDocument::route('/create'),
            'view' => Pages\ViewIdentityDocument::route('/{record}'),
            'edit' => Pages\EditIdentityDocument::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['document_number', 'user.email', 'documentType.name'];
    }
}
