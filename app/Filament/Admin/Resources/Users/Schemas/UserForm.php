<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('User Management')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Basic Information')
                            ->schema([
                                Section::make('Personal Details')
                                    ->description('Basic user information and profile settings')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull()
                                            ->placeholder('Enter full name'),

                                        TextInput::make('email')
                                            ->label('Email Address')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->placeholder('user@example.com'),

                                        TextInput::make('password')
                                            ->password()
                                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                                            ->dehydrated(fn ($state) => filled($state))
                                            ->required(fn (string $context): bool => $context === 'create')
                                            ->maxLength(255)
                                            ->placeholder('Enter password')
                                            ->helperText('Leave blank to keep current password (when editing)'),

                                        // Security: ->image() means
                                        // acceptedFileTypes(['image/*']), which
                                        // accepts image/svg+xml -- an XML doc that
                                        // can carry <script>. This field lands on
                                        // the default (private, non-web-served) disk
                                        // today so it is not currently an XSS vector,
                                        // but the allowlist is the cheap half of the
                                        // fix and survives the disk being re-pointed.
                                        // Validates the *upload*; does not make the
                                        // serving origin safe. maxSize stays 2MB.
                                        FileUpload::make('profile_photo_path')
                                            ->label('Profile Photo')
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                                            ->imageEditor()
                                            ->maxSize(2048)
                                            ->directory('profile-photos')
                                            ->columnSpanFull()
                                            ->helperText('Upload a profile photo (max 2MB)'),
                                    ]),
                            ]),

                        Tab::make('Roles & Permissions')
                            ->schema([
                                Section::make('Role Assignment')
                                    ->description('Assign roles to control user access and permissions')
                                    ->schema([
                                        Select::make('roles')
                                            ->relationship('roles', 'name')
                                            ->multiple()
                                            ->preload()
                                            ->searchable()
                                            ->placeholder('Select roles')
                                            ->helperText('Users inherit all permissions from their assigned roles')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                    ]),
            ]);
    }
}
