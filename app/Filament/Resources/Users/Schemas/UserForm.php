<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\System\SystemLanguage;
use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make("Asosiy")
                    ->description("Asosiy Ma'lumotlar")
                    ->components([
                        Tabs::make('contact')
                            ->tabs([
                                Tab::make('Email')
                                    ->schema([
                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->placeholder('Email kiriting'),
                                    ]),

                                Tab::make('Telefon')
                                    ->schema([
                                        TextInput::make('phone_number')
                                            ->label('Telefon raqam')
                                            ->tel()
                                            ->placeholder('+998 xx xxx xx xx'),
                                    ]),
                            ]),

                        Hidden::make('contact_required')
                            ->dehydrated(false)
                            ->rule(function ($get) {
                                return fn () =>
                                    filled($get('email')) || filled($get('phone'));
                            }),

                        TextInput::make('password')
                            ->label('Parol')
                            ->password()
                            ->disabled(fn ($get) => ! $get('change_password'))
                            ->required(fn ($get) => $get('change_password'))
                            ->dehydrated(fn ($get) => $get('change_password'))
                            ->suffixAction(
                        Action::make('changePassword')
                                    ->icon('heroicon-m-pencil-square')
                                    ->tooltip('Parolni o‘zgartirish')
                                    ->action(fn ($set, $get) =>
                                        $set('change_password', ! $get('change_password'))
                                    )
                            ),

                        Select::make('role')
                            ->label('Roli')
                            ->options(UserRole::options())
                            ->required()
                            ->native(false),
                    ]),

                Section::make('Qo‘shimcha')
                    ->description("Qo'shimcha Ma'lumotlar")
                    ->columns(2)
                    ->components([
                        FileUpload::make('image')
                                ->label('Foydalanuvchi Rasmi')
                                ->image()
                                ->directory('users')
                                ->columnSpan(2),

                        Select::make('status')
                            ->label('Holat')
                            ->options(UserStatus::options())
                            ->required()
                            ->columnSpan(1),

                        Select::make('lang')
                            ->required()
                            ->label('Til')
                            ->options(SystemLanguage::options())
                            ->columnSpan(1),

                        DateTimePicker::make('last_login_at')
                            ->disabled()
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
