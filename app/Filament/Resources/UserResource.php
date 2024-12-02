<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Parfaitementweb\FilamentCountryField\Forms\Components\Country;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)->unique(ignoreRecord: true),
                TextInput::make('surname')->label('Surname')->required()->maxLength(255),
                Country::make('country'),
                TextInput::make('affiliation')->label('Affiliation')->required()->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn($state) => bcrypt($state)),
                Select::make('roles')
                    ->multiple()
                    ->relationship('roles', 'name') // This dropdown shows roles
                    ->required(),
            ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('email')->sortable()->searchable(),
                TextColumn::make('surname')->sortable()->searchable(),
                TextColumn::make('country')->sortable()->searchable(),
                TextColumn::make('affiliation')->sortable()->searchable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')->dateTime('M d, Y')->sortable(),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                // Define any filters if needed
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function beforeDelete(User $record): void
    {
        // Nullify associate_editor_id in papers before deletion
        $record->papersAsEditor()->update(['associate_editor_id' => null]);
    }
    public static function afterSave(Form $form, $record)
    {
        $record->syncRoles($form->getState()['roles']);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool
    {
        if (auth()->check())
            return auth()->user()->hasAnyRole(['super_admin']);
        else
            return false;
    }
}
