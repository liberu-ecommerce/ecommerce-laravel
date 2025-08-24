<?php

namespace App\Filament\App\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\ProductReviewResource\Pages\ListProductReviews;
use App\Filament\App\Resources\ProductReviewResource\Pages\CreateProductReview;
use App\Filament\App\Resources\ProductReviewResource\Pages\EditProductReview;
use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use App\Models\Customer;
use Filament\Tables\Table;
use App\Models\ProductReview;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\App\Resources\ProductReviewResource\Pages;
use App\Filament\App\Resources\ProductReviewResource\RelationManagers;

class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $modelLabel = "Review";

    protected static ?int $navigationSort = 8;

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
                    ->options(Customer::pluck('first_name', 'id'))
                    ->reactive(),

                Textarea::make('comments')
                    ->label('Comments')
                    ->required()
                    ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.first_name')
                    ->label('Customer'),
                TextColumn::make('comments')
                    ->label('Comments'),
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
            'index' => ListProductReviews::route('/'),
            'create' => CreateProductReview::route('/create'),
            'edit' => EditProductReview::route('/{record}/edit'),
        ];
    }
}
