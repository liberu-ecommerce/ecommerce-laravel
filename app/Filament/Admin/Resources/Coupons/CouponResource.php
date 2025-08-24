<?php

namespace App\Filament\Admin\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Admin\Resources\CouponResource\Pages\ListCoupons;
use App\Filament\Admin\Resources\CouponResource\Pages\CreateCoupon;
use App\Filament\Admin\Resources\CouponResource\Pages\EditCoupon;
use App\Filament\Admin\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('type')
                    ->required()
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed Amount',
                    ]),
                TextInput::make('value')
                    ->required()
                    ->numeric()
                    ->label(fn ($get) => $get('type') === 'percentage' ? 'Discount Percentage' : 'Discount Amount'),
                DateTimePicker::make('valid_from')
                    ->required(),
                DateTimePicker::make('valid_until')
                    ->required(),
                TextInput::make('max_uses')
                    ->numeric()
                    ->nullable(),
                TextInput::make('min_purchase_amount')
                    ->numeric()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable(),
                TextColumn::make('type'),
                TextColumn::make('value'),
                TextColumn::make('valid_from')->date(),
                TextColumn::make('valid_until')->date(),
                TextColumn::make('uses_count')->counts('orders'),
                TextColumn::make('max_uses'),
            ])
            ->filters([
                Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('valid_until', '>=', now())),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
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
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }
}