<?php

namespace App\Livewire\Couriers;

use App\Models\CourierProfile;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class CourierStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $stats = Cache::remember(
            'courier_stats_widget',
            now()->addMinutes(5),
            function () {
                return [
                    'online'  => CourierProfile::where('is_online', true)->count(),
                    'offline' => CourierProfile::where('is_online', false)->count(),
                    'active'  => CourierProfile::where('is_active', true)->count(),
                ];
            }
        );

        return [
            Stat::make('Online courierlar', $stats['online'])
                ->icon('heroicon-o-signal')
                ->color('success'),

            Stat::make('Offline courierlar', $stats['offline'])
                ->icon('heroicon-o-signal-slash')
                ->color('gray'),

            Stat::make('Hozir ishda', $stats['active'])
                ->icon('heroicon-o-briefcase')
                ->color('info'),
        ];
    }
}