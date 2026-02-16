<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerSegmentResource\Pages;
use App\Models\CustomerSegment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerSegmentResource extends Resource
{
    protected static ?string $model = CustomerSegment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('match_type')
                            ->options([
                                'all' => 'Match ALL conditions',
                                'any' => 'Match ANY condition',
                            ])
                            ->default('all')
                            ->required(),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Conditions')
                    ->schema([
                        Forms\Components\Repeater::make('conditions')
                            ->schema([
                                Forms\Components\Select::make('field')
                                    ->options([
                                        'total_orders' => 'Total Orders',
                                        'lifetime_value' => 'Lifetime Value',
                                        'last_order_date' => 'Last Order Date',
                                        'in_customer_group' => 'Customer Group',
                                    ])
                                    ->required(),
                                
                                Forms\Components\Select::make('operator')
                                    ->options([
                                        '=' => 'Equals',
                                        '!=' => 'Not Equals',
                                        '>' => 'Greater Than',
                                        '>=' => 'Greater or Equal',
                                        '<' => 'Less Than',
                                        '<=' => 'Less or Equal',
                                    ])
                                    ->required(),
                                
                                Forms\Components\TextInput::make('value')
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('customer_count')
                    ->label('Members')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('match_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'all' => 'success',
                        'any' => 'warning',
                    }),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('last_calculated_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\Action::make('calculate')
                    ->icon('heroicon-o-calculator')
                    ->action(fn (CustomerSegment $record) => $record->calculateMembers())
                    ->requiresConfirmation(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerSegments::route('/'),
            'create' => Pages\CreateCustomerSegment::route('/create'),
            'edit' => Pages\EditCustomerSegment::route('/{record}/edit'),
        ];
    }
}
