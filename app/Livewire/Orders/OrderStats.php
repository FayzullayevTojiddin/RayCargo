<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use App\Enums\Order\OrderStatus;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class OrderStats extends StatsOverviewWidget
{    
    protected function getColumns(): int | array
    {
        return 5;
    }
    protected function getStats(): array
    {
        $stats = Cache::remember(
            'order_stats_widget',
            now()->addMinutes(5),
            function () {
                return [
                    OrderStatus::CREATED->value     => Order::where('status', OrderStatus::CREATED)->count(),
                    OrderStatus::ACCEPTED->value    => Order::where('status', OrderStatus::ACCEPTED)->count(),
                    OrderStatus::IN_PROGRESS->value => Order::where('status', OrderStatus::IN_PROGRESS)->count(),
                    OrderStatus::COMPLETED->value   => Order::where('status', OrderStatus::COMPLETED)->count(),
                    OrderStatus::CANCELLED->value   => Order::where('status', OrderStatus::CANCELLED)->count(),
                ];
            }
        );

        return [
            Stat::make(OrderStatus::CREATED->label(), $stats[OrderStatus::CREATED->value])
                ->description('Kutilayotgan buyurtmalar')
                ->icon('heroicon-o-clock')
                ->color('gray'),

            Stat::make(OrderStatus::ACCEPTED->label(), $stats[OrderStatus::ACCEPTED->value])
                ->description('Qabul qilingan buyurtmalar')
                ->icon('heroicon-o-check-circle')
                ->color('info'),

            Stat::make(OrderStatus::IN_PROGRESS->label(), $stats[OrderStatus::IN_PROGRESS->value])
                ->description('Hozir bajarilayotgan buyurtmalar')
                ->icon('heroicon-o-arrow-path')
                ->color('warning'),

            Stat::make(OrderStatus::COMPLETED->label(), $stats[OrderStatus::COMPLETED->value])
                ->description('Muvaffaqiyatli buyurtmalar')
                ->icon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make(OrderStatus::CANCELLED->label(), $stats[OrderStatus::CANCELLED->value])
                ->description('Bekor qilingan buyurtmalar')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}