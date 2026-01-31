<?php

namespace App\Livewire\Widgets\Users;

use App\Models\User;
use App\Enums\User\UserRole;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class UsersByRoleStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $stats = Cache::remember(
            'users_by_role_stats',
            now()->addMinutes(10),
            function () {
                return collect(UserRole::cases())
                    ->mapWithKeys(fn (UserRole $role) => [
                        $role->value => User::where('role', $role)->count(),
                    ])
                    ->toArray();
            }
        );

        return [
            Stat::make('Administratorlar', $stats[UserRole::ADMIN->value] ?? 0)
                ->icon('heroicon-o-shield-check')
                ->color('danger'),

            Stat::make('Kuryerlar', $stats[UserRole::COURIER->value] ?? 0)
                ->icon('heroicon-o-truck')
                ->color('warning'),

            Stat::make('Xodimlar', $stats[UserRole::WORKER->value] ?? 0)
                ->icon('heroicon-o-briefcase')
                ->color('info'),

            Stat::make('Mijozlar', $stats[UserRole::CLIENT->value] ?? 0)
                ->icon('heroicon-o-users')
                ->color('success'),
        ];
    }
}