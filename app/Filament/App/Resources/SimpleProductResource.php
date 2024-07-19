<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SimpleProductResource\Pages;
use App\Filament\Admin\Resources\SimpleProductResource\RelationManagers;
use App\Models\SimpleProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SimpleProductResource extends Resource
{
    protected static ?string $model = SimpleProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('quantity')
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->sortable()
                    ->searchable(),
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
            'index' => Pages\ListSimpleProducts::route('/'),
            'create' => Pages\CreateSimpleProduct::route('/create'),
            'edit' => Pages\EditSimpleProduct::route('/{record}/edit'),
        ];
    }
}
