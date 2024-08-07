<?php
namespace App\Filament\App\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use App\Models\ProductTag;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\App\Resources\ProductTagResource\Pages;
use App\Filament\App\Resources\ProductTagResource\RelationManagers;

class ProductTagResource extends Resource
{
    protected static ?string $model = ProductTag::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->required()
                    ->options(fn() => Product::pluck('name', 'id')->toArray())
                    ->reactive(),

                Forms\Components\Select::make('tag_id')
                    ->label('Tag')
                    ->required()
                    ->options(fn() => ProductTag::pluck('name', 'id')->toArray())
                    ->reactive()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->label('Product'),
                Tables\Columns\TextColumn::make('tag.name')->label('Tag'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductTags::route('/'),
            'create' => Pages\CreateProductTag::route('/create'),
            'edit' => Pages\EditProductTag::route('/{record}/edit'),
        ];
    }
}
