<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Personal')
                    ->schema([
                        TextInput::make('first_name')
                            ->label('Nombre(s)')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('paternal_surname')
                            ->label('Apellido Paterno')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('maternal_surname')
                            ->label('Apellido Materno')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true), // Ignorar el registro actual al editar
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('curp')
                            ->label('CURP')
                            ->maxLength(18),
                        DatePicker::make('date_of_birth')
                            ->label('Fecha de Nacimiento')
                            ->native(false) // Para usar un selector de fecha más amigable
                            ->maxDate(now()), // No permitir fechas futuras
                        TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->dehydrateStateUsing(fn (string $state): string => \Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Configuración de Usuario')
                    ->schema([
                        // Toggle::make('is_admin') // Eliminado para que no se pueda hacer admin desde este recurso
                        //     ->label('Es Administrador')
                        //     ->helperText('Activar para otorgar permisos de administrador.'),
                        Toggle::make('email_verified_at')
                            ->label('Correo Verificado')
                            ->helperText('Marcar si el correo electrónico del usuario ha sido verificado.')
                            ->dehydrateStateUsing(fn (?bool $state): ?string => $state ? now() : null)
                            ->dehydrated(fn (?bool $state): bool => $state !== null),
                        Toggle::make('phone_verified_at')
                            ->label('Teléfono Verificado')
                            ->helperText('Marcar si el número de teléfono del usuario ha sido verificado.')
                            ->dehydrateStateUsing(fn (?bool $state): ?string => $state ? now() : null)
                            ->dehydrated(fn (?bool $state): bool => $state !== null),
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
                TextColumn::make('full_name') // Usamos el accesor full_name del modelo
                ->label('Nombre Completo')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean(),
                IconColumn::make('email_verified_at')
                    ->label('Email Verificado')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('phone_verified_at')
                    ->label('Teléfono Verificado')
                    ->boolean()
                    ->sortable(),
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
                SelectFilter::make('is_admin')
                    ->label('Tipo de Usuario')
                    ->options([
                        true => 'Administradores',
                        false => 'Usuarios Normales',
                    ]),
                Tables\Filters\Filter::make('email_verified_at')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at'))
                    ->label('Email Verificado'),
                Tables\Filters\Filter::make('phone_verified_at')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('phone_verified_at'))
                    ->label('Teléfono Verificado'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            RelationManagers\IdentityDocumentsRelationManager::class, // Registrado el Relation Manager
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'paternal_surname', 'maternal_surname', 'email', 'phone', 'curp'];
    }

    // Add this method to filter out admin users by default
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_admin', false);
    }
}
