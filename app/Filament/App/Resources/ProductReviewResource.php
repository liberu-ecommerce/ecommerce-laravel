<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProductReview;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\ProductReviewResource\Pages;
use App\Filament\Admin\Resources\ProductReviewResource\RelationManagers;

class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

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
                    ->options(Customer::pluck('first_name', 'id'))
                    ->reactive(),

                Forms\Components\Textarea::make('comments')
                    ->label('Comments')
                    ->required()
                    ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.first_name')
                    ->label('Customer'),
                Tables\Columns\TextColumn::make('comments')
                    ->label('Comments'),
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
            'index' => Pages\ListProductReviews::route('/'),
            'create' => Pages\CreateProductReview::route('/create'),
            'edit' => Pages\EditProductReview::route('/{record}/edit'),
        ];
    }
}
