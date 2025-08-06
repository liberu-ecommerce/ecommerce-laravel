<?php

namespace App\Filament\App\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\OrderResource\Pages\ListOrders;
use App\Filament\App\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\App\Resources\OrderResource\Pages\EditOrder;
use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Customer;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\App\Resources\OrderResource\Pages;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('customer_id')
                ->label('Customer')
                ->required()
                ->options(fn() => Customer::pluck('first_name', 'id')->toArray())
                ->reactive(),

            DatePicker::make('order_date')
                ->label('Order Date')
                ->required(),

            TextInput::make('total_amount')
                ->label('Total Amount')
                ->required()
                ->numeric(),

            Select::make('payment_status')
                ->label('Payment Status')
                ->required()
                ->options([
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'cancelled' => 'Cancelled',
                ]),

            Select::make('shipping_status')
                ->label('Shipping Status')
                ->required()
                ->options([
                    'pending' => 'Pending',
                    'shipped' => 'Shipped',
                    'delivered' => 'Delivered',
                    'returned' => 'Returned',
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('customer.first_name')
                ->label('Customer'),
            TextColumn::make('order_date')
                ->label('Order Date'),
            TextColumn::make('total_amount')
                ->label('Total Amount'),
            TextColumn::make('payment_status')
                ->label('Payment Status'),
            TextColumn::make('shipping_status')
                ->label('Shipping Status'),
        ])
        ->filters([])
        ->recordActions([
            EditAction::make(),
        ])
        ->toolbarActions([
            DeleteBulkAction::make(),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
