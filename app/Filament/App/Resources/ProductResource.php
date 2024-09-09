<?php

namespace App\Filament\App\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
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

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('short_description')
                    ->maxLength(255),

                Forms\Components\Textarea::make('long_description')
                    ->maxLength(65535),

                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->required()
                    ->options(ProductCategory::pluck('name', 'id'))
                    ->reactive(),

                Forms\Components\Toggle::make('is_variable')
                    ->label('Variable'),
                Forms\Components\Toggle::make('is_grouped')
                    ->label('Grouped'),
                Forms\Components\Toggle::make('is_simple')
                    ->label('Simple'),

                Forms\Components\FileUpload::make('featured_image')
                    ->image()
                    ->label('Featured Image'),
                    
                Forms\Components\TextInput::make('meta_title')
                    ->label('Meta Title')
                    ->maxLength(60)
                    ->helperText('Recommended length: 50-60 characters'),

                Forms\Components\Textarea::make('meta_description')
                    ->label('Meta Description')
                    ->maxLength(160)
                    ->helperText('Recommended length: 150-160 characters'),

                Forms\Components\TagsInput::make('meta_keywords')
                    ->label('Meta Keywords')
                    ->helperText('Enter keywords separated by commas'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('short_description')
                    ->label('Short Description'),
                Tables\Columns\TextColumn::make('long_description')
                    ->label('Long Description'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category'),
                Tables\Columns\TextColumn::make('is_variable')
                    ->label('Variable'),
                Tables\Columns\TextColumn::make('is_grouped')
                    ->label('Grouped'),
                Tables\Columns\TextColumn::make('is_simple')
                    ->label('Simple'),
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label('Featured Image'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
