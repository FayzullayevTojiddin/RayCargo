<?php

namespace App\Livewire\VehicleType;

use App\Models\VehicleType;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class VehicleTypesStat extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $stats = Cache::remember(
            'vehicle_types_stats_widget',
            now()->addMinutes(10),
            function () {
                return [
                    'total'    => VehicleType::count(),
                    'active'   => VehicleType::where('is_active', true)->count(),
                    'inactive' => VehicleType::where('is_active', false)->count(),
                ];
            }
        );

        return [
            Stat::make('Transport turlari', $stats['total'])
                ->description('Umumiy transport turlari soni')
                ->icon('heroicon-o-squares-2x2')
                ->color('primary'),

            Stat::make('Faol transportlar', $stats['active'])
                ->description('Hozirda foydalanilayotgan turlar')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Faol emas', $stats['inactive'])
                ->description('Vaqtincha o‘chirilgan turlar')
                ->icon('heroicon-o-x-circle')
                ->color('gray'),
        ];
    }

    protected function getColumns(): int|array
    {
        return 3;
    }
}