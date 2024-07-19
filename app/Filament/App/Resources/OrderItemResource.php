<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms\Form;
use App\Models\OrderItem;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\OrderItemResource\Pages;
use App\Filament\Admin\Resources\OrderItemResource\RelationManagers;

class OrderItemResource extends Resource
{
    protected static ?string $model = OrderItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->label('Order')
                    ->required()
                    ->options(Order::pluck('id', 'id'))
                    ->reactive(),

                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->required()
                    ->options(Product::pluck('name', 'id'))
                    ->reactive(),

                Forms\Components\TextInput::make('quantity')
                    ->label('Quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1),

                Forms\Components\TextInput::make('price')
                    ->label('Price')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_date')
                    ->label('Order Date'),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity'),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price'),
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
            'index' => Pages\ListOrderItems::route('/'),
            'create' => Pages\CreateOrderItem::route('/create'),
            'edit' => Pages\EditOrderItem::route('/{record}/edit'),
        ];
    }
}
