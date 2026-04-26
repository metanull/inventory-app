<?php

namespace App\Filament\Pages;

use App\Enums\Permission;
use App\Filament\Resources\CollectionResource;
use App\Filament\Resources\ItemResource;
use App\Filament\Resources\PartnerResource;
use App\Filament\Widgets\InventoryOverviewWidget;
use App\Filament\Widgets\RecentItemsWidget;
use App\Models\Collection;
use App\Models\Item;
use App\Models\Partner;
use Filament\Actions\Action;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected function getHeaderActions(): array
    {
        $actions = [];

        $user = auth()->user();

        if ($user?->can('create', Item::class)) {
            $actions[] = Action::make('newItem')
                ->label('New Item')
                ->icon('heroicon-o-cube')
                ->url(ItemResource::getUrl('create'));
        }

        if ($user?->can('create', Collection::class)) {
            $actions[] = Action::make('newCollection')
                ->label('New Collection')
                ->icon('heroicon-o-archive-box')
                ->url(CollectionResource::getUrl('create'));
        }

        if ($user?->can('create', Partner::class)) {
            $actions[] = Action::make('newPartner')
                ->label('New Partner')
                ->icon('heroicon-o-building-library')
                ->url(PartnerResource::getUrl('create'));
        }

        return $actions;
    }

    public function getWidgets(): array
    {
        return [
            InventoryOverviewWidget::class,
            RecentItemsWidget::class,
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::ACCESS_ADMIN_PANEL->value) ?? false;
    }
}
