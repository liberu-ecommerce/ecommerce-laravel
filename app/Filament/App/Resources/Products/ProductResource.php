<?php

namespace App\Filament\App\Resources\Products;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\Products\Pages\ListProducts;
use App\Filament\App\Resources\Products\Pages\CreateProduct;
use App\Filament\App\Resources\Products\Pages\EditProduct;
use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Tables\Table;
use App\Models\ProductCategory;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\App\Resources\ProductResource\Pages;
use App\Filament\App\Resources\ProductResource\RelationManagers;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('category_id')
                    ->label('Category')
                    ->required()
                    ->relationship('category', 'name')
                    ->reactive(),

                TextInput::make('short_description')
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('long_description')
                    ->maxLength(65535)
                    ->columnSpanFull(),


                Grid::make()
                    ->schema([
                        Toggle::make('is_variable')
                            ->label('Variable'),
                        Toggle::make('is_grouped')
                            ->label('Grouped'),
                        Toggle::make('is_simple')
                            ->label('Simple'),
                    ]),

                FileUpload::make('featured_image')
                    ->image()
                    ->disk('public')
                    ->directory('products')
                    ->visibility('public')
                    ->label('Featured Image'),


                Repeater::make('images')
                    ->relationship('images')
                    ->schema([
                        FileUpload::make('image')
                            ->hiddenLabel()
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')

                    ]),







                // Forms\Components\TextInput::make('meta_title')
                //     ->label('Meta Title')
                //     ->maxLength(60)
                //     ->helperText('Recommended length: 50-60 characters'),

                // Forms\Components\Textarea::make('meta_description')
                //     ->label('Meta Description')
                //     ->maxLength(160)
                //     ->helperText('Recommended length: 150-160 characters'),

                // Forms\Components\TagsInput::make('meta_keywords')
                //     ->label('Meta Keywords')
                //     ->helperText('Enter keywords separated by commas'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image')
                    ->label('Featured Image'),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('short_description')
                //     ->label('Short Description'),
                // Tables\Columns\TextColumn::make('long_description')
                //     ->label('Long Description'),
                TextColumn::make('category.name')
                    ->label('Category'),
                // Tables\Columns\TextColumn::make('is_variable')
                //     ->label('Variable'),
                // Tables\Columns\TextColumn::make('is_grouped')
                //     ->label('Grouped'),
                // Tables\Columns\TextColumn::make('is_simple')
                //     ->label('Simple'),
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
