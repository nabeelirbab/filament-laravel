<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FieldResource\Pages;
use App\Filament\Resources\FieldResource\RelationManagers;
use App\Models\Field;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FieldResource extends Resource
{
    protected static ?string $model = Field::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Field Name')
                    ->required()
                    ->unique(Field::class, 'name') // Ensures each field name is unique
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListFields::route('/'),
            'create' => Pages\CreateField::route('/create'),
            'edit' => Pages\EditField::route('/{record}/edit'),
        ];
    }

    // Only super admin can access this resource
    public static function canViewAny(): bool
    {
        if (auth()->check())
            return auth()->user()->hasAnyRole(['super_admin']);
        else
            return false;
    }

    public static function canCreate(): bool
    {
        if (auth()->check())
            return auth()->user()->hasAnyRole(['super_admin']);
        else
            return false;
    }

    public static function canEdit(Model $record): bool
    {
        if (auth()->check())
            return auth()->user()->hasAnyRole(['super_admin']);
        else
            return false;
    }

    public static function canDelete(Model $record): bool
    {
        if (auth()->check())
            return auth()->user()->hasAnyRole(['super_admin']);
        else
            return false;
    }
}
