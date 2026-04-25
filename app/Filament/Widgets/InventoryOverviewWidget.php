<?php

namespace App\Filament\Widgets;

use App\Enums\Permission;
use App\Models\Collection;
use App\Models\Item;
use App\Models\Partner;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::VIEW_DATA->value) ?? false;
    }

    protected function getStats(): array
    {
        $stats = [
            Stat::make('Items', (string) Item::query()->count())
                ->icon('heroicon-o-cube'),
            Stat::make('Collections', (string) Collection::query()->count())
                ->icon('heroicon-o-archive-box'),
            Stat::make('Partners', (string) Partner::query()->count())
                ->icon('heroicon-o-building-library'),
        ];

        if (auth()->user()?->hasPermissionTo(Permission::MANAGE_USERS->value)) {
            $stats[] = Stat::make('Pending Registrations', (string) User::query()->whereNull('approved_at')->count())
                ->icon('heroicon-o-clock')
                ->color('warning');
        }

        return $stats;
    }
}
