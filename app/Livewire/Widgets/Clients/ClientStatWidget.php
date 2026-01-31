<?php

namespace App\Livewire\Widgets\Clients;

use App\Models\ClientProfile;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ClientStatWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '1m';

    protected function getStats(): array
    {
        $stats = Cache::remember('client_stats_widget', now()->addMinutes(10), function () {
            return [
                'total' => ClientProfile::count(),

                'week' => ClientProfile::whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ])->count(),

                'today' => ClientProfile::whereDate(
                    'created_at',
                    Carbon::today()
                )->count(),
            ];
        });

        return [
            Stat::make('Umumiy mijozlar', $stats['total'])
                ->icon('heroicon-o-users'),

            Stat::make('Shu hafta qo‘shilganlar', $stats['week'])
                ->icon('heroicon-o-calendar'),

            Stat::make('Bugun qo‘shilganlar', $stats['today'])
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
