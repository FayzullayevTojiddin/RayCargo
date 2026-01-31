<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\RelationManagers\NotificationsRelationManager;
use App\Filament\Resources\Users\RelationManagers\WalletTransactionsRelationManager;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::Users;

    protected static string | UnitEnum | null $navigationGroup = "Tizim";

    protected static ?string $navigationLabel = 'Foydalanuvchilar';

    protected static ?string $pluralModelLabel = 'Foydalanuvchilar';

    protected static ?string $modelLabel = 'Foydalanuvchi';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            NotificationsRelationManager::class,
            WalletTransactionsRelationManager::class
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount([
                'notifications',
                'notifications as unread_notifications_count' => function ($query) {
                    $query->where('is_read', false);
                }
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }
}
