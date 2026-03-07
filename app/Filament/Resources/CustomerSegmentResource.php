<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerSegmentResource\Pages;
use App\Models\CustomerSegment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerSegmentResource extends Resource
{
    protected static ?string $model = CustomerSegment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
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

                Section::make('Conditions')
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
                        default => 'gray',
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
            ->recordActions([
                Action::make('calculate')
                    ->icon('heroicon-o-calculator')
                    ->action(fn (CustomerSegment $record) => $record->calculateMembers())
                    ->requiresConfirmation(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
