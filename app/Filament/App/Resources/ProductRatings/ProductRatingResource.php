<?php

namespace App\Filament\App\Resources\ProductRatings;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\ProductRatings\Pages\ListProductRatings;
use App\Filament\App\Resources\ProductRatings\Pages\CreateProductRating;
use App\Filament\App\Resources\ProductRatings\Pages\EditProductRating;
use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use App\Models\Customer;
use Filament\Tables\Table;
use App\Models\ProductRating;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\App\Resources\ProductRatingResource\Pages;
use App\Filament\App\Resources\ProductRatingResource\RelationManagers;

class ProductRatingResource extends Resource
{
    protected static ?string $model = ProductRating::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Product')
                    ->required()
                    ->options(Product::pluck('name', 'id'))
                    ->reactive(),

                Select::make('customer_id')
                    ->label('Customer')
                    ->required()
                    ->options(Customer::pluck('name', 'id'))
                    ->reactive(),

                TextInput::make('rating')
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
                TextColumn::make('product.name')
                    ->label('Product'),
                TextColumn::make('customer.first_name')
                    ->label('Customer'),
                TextColumn::make('rating')
                    ->label('Rating'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListProductRatings::route('/'),
            'create' => CreateProductRating::route('/create'),
            'edit' => EditProductRating::route('/{record}/edit'),
        ];
    }
}
