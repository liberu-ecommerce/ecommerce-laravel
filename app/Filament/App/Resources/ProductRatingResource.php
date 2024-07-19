<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProductRating;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\ProductRatingResource\Pages;
use App\Filament\Admin\Resources\ProductRatingResource\RelationManagers;

class ProductRatingResource extends Resource
{
    protected static ?string $model = ProductRating::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->required()
                    ->options(Product::pluck('name', 'id'))
                    ->reactive(),

                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->required()
                    ->options(Customer::pluck('name', 'id'))
                    ->reactive(),

                Forms\Components\TextInput::make('rating')
                    ->label('Rating')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product'),
                Tables\Columns\TextColumn::make('customer.first_name')
                    ->label('Customer'),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating'),
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
            'index' => Pages\ListProductRatings::route('/'),
            'create' => Pages\CreateProductRating::route('/create'),
            'edit' => Pages\EditProductRating::route('/{record}/edit'),
        ];
    }
}
