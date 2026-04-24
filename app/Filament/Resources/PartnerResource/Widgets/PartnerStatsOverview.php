<?php

namespace App\Filament\Resources\PartnerResource\Widgets;

use App\Models\Partner;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PartnerStatsOverview extends StatsOverviewWidget
{
    public ?Partner $record = null;

    protected function getStats(): array
    {
        $partner = $this->record?->loadCount([
            'items',
            'collections',
        ]);

        return [
            Stat::make('Owned items', (string) ($partner?->items_count ?? 0)),
            Stat::make('Collection participations', (string) ($partner?->collections_count ?? 0)),
        ];
    }
}
