<?php

namespace App\Filament\App\Resources\Invoices;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\Invoices\Pages\ListInvoices;
use App\Filament\App\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\App\Resources\Invoices\Pages\EditInvoice;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Customer;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\App\Resources\InvoiceResource\Pages;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('customer_id')
                ->label('Customer')
                ->required()
                ->options(Customer::pluck('name', 'id'))
                ->reactive(),
            Select::make('order_id')
                ->label('Order')
                ->required()
                ->options(Order::pluck('id', 'id'))
                ->reactive(),
            DatePicker::make('invoice_date')
                ->label('Invoice Date')
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
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
                TextColumn::make('customer.name')
                    ->label('Customer'),
                TextColumn::make('order.id')
                    ->label('Order'),
                TextColumn::make('invoice_date')
                    ->label('Invoice Date'),
                TextColumn::make('total_amount')
                    ->label('Total Amount'),
                TextColumn::make('payment_status')
                    ->label('Payment Status'),
            ])
            ->filters([
                // Define filters here if needed
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
            // Define relations here if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'edit' => EditInvoice::route('/{record}/edit'),
        ];
    }
}
