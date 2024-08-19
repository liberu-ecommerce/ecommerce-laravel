<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductTag;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->options(ProductCategory::all()->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\TextInput::make('inventory_count')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                Forms\Components\Select::make('tags')
                    ->multiple()
                    ->relationship('tags', 'name')
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('price')->money('usd')->sortable(),
                Tables\Columns\TextColumn::make('category.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('inventory_count')->sortable(),
                Tables\Columns\TagsColumn::make('tags.name'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkAction::make('export')
                    ->label('Export Selected')
                    ->icon('heroicon-o-download')
                    ->action(fn (Collection $records) => static::export($records)),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    protected static function export(Collection $records)
    {
        // Implement export functionality here
    }
}