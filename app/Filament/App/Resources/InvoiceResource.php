<?php

namespace App\Filament\App\Resources;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\App\Resources\InvoiceResource\Pages;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('customer_id')
                ->label('Customer')
                ->required()
                ->options(Customer::pluck('name', 'id'))
                ->reactive(),
            Forms\Components\Select::make('order_id')
                ->label('Order')
                ->required()
                ->options(Order::pluck('id', 'id'))
                ->reactive(),
            Forms\Components\DatePicker::make('invoice_date')
                ->label('Invoice Date')
                ->required(),
            Forms\Components\TextInput::make('total_amount')
                ->label('Total Amount')
                ->required()
                ->numeric(),
            Forms\Components\Select::make('payment_status')
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
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer'),
                Tables\Columns\TextColumn::make('order.id')
                    ->label('Order'),
                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Invoice Date'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status'),
            ])
            ->filters([
                // Define filters here if needed
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
            // Define relations here if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
