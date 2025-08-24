<?php

namespace App\Filament\Admin\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use App\Filament\Admin\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Admin\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Admin\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductTag;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use League\Csv\Writer;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->maxLength(65535),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Select::make('category_id')
                    ->label('Category')
                    ->options(ProductCategory::all()->pluck('name', 'id'))
                    ->searchable(),
                TextInput::make('inventory_count')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('low_stock_threshold')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->label('Low Stock Threshold'),
                Select::make('tags')
                    ->multiple()
                    ->relationship('tags', 'name')
                    ->preload(),
                Section::make('Pricing')
                    ->schema([
                        Select::make('pricing_type')
                            ->options([
                                'fixed' => 'Fixed Price',
                                'free' => 'Free',
                                'donation' => 'Pay What You Want',
                            ])
                            ->default('fixed')
                            ->reactive(),
                            
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->visible(fn (callable $get) => $get('pricing_type') === 'fixed'),
                            
                        TextInput::make('suggested_price')
                            ->numeric()
                            ->prefix('$')
                            ->visible(fn (callable $get) => $get('pricing_type') === 'donation'),
                            
                        TextInput::make('minimum_price')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->visible(fn (callable $get) => $get('pricing_type') === 'donation'),
                    ]),
                Section::make('Downloadable Product')
                    ->schema([
                        Toggle::make('is_downloadable')
                            ->label('Is Downloadable Product')
                            ->reactive(),

                        FileUpload::make('downloadable_file')
                            ->label('Product File')
                            ->disk('local')
                            ->directory('downloadable_products')
                            ->visibility('private')
                            ->acceptedFileTypes(['application/pdf', 'application/zip'])
                            ->maxSize(50 * 1024) // 50MB
                            ->visible(fn (callable $get) => $get('is_downloadable')),

                        TextInput::make('download_limit')
                            ->label('Download Limit')
                            ->numeric()
                            ->minValue(1)
                            ->visible(fn (callable $get) => $get('is_downloadable')),

                        DateTimePicker::make('expiration_time')
                            ->label('Download Expiration')
                            ->visible(fn (callable $get) => $get('is_downloadable')),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('price')->money('usd')->sortable(),
                TextColumn::make('category.name')->searchable()->sortable(),
                TextColumn::make('inventory_count')->sortable(),
                // Tables\Columns\TagsColumn::make('tags.name'),
            ])
            // ->filters([
            //     Tables\Filters\SelectFilter::make('category')
            //         ->relationship('category', 'name'),
            // ])
            ->recordActions([
                EditAction::make(),
                Action::make('adjustInventory')
                    ->label('Adjust Inventory')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->action(function (Product $record, array $data): void {
                        $record->inventory_count += $data['adjustment'];
                        $record->save();

                        // InventoryLog::create([
                        //     'product_id' => $record->id,
                        //     'quantity_change' => $data['adjustment'],
                        //     'reason' => $data['reason'],
                        // ]);
                    })
                    ->schema([
                        TextInput::make('adjustment')
                            ->label('Quantity Adjustment')
                            ->required()
                            ->integer(),
                        TextInput::make('reason')
                            ->label('Reason for Adjustment')
                            ->required(),
                    ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
                BulkAction::make('export')
                    ->label('Export Selected')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (Collection $records) => static::export($records)),
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    protected static function export(Collection $records)
    {
        $csv = Writer::createFromString('');
        
        $csv->insertOne(['Name', 'SKU', 'Category', 'Price', 'Inventory Count', 'Low Stock Threshold', 'Status']);
        
        foreach ($records as $record) {
            $csv->insertOne([
                $record->name,
                $record->sku,
                $record->category->name,
                $record->price,
                $record->inventory_count,
                $record->low_stock_threshold,
                $record->inventory_count > $record->low_stock_threshold ? 'In Stock' : ($record->inventory_count > 0 ? 'Low Stock' : 'Out of Stock'),
            ]);
        }
        
        $filename = 'inventory_report_' . date('Y-m-d') . '.csv';
        $path = storage_path('app/public/' . $filename);
        file_put_contents($path, $csv->getContent());
        
        return response()->download($path)->deleteFileAfterSend();
    }
}