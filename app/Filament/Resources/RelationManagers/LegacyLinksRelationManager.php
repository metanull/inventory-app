<?php

namespace App\Filament\Resources\RelationManagers;

use App\Filament\Concerns\HasLegacyLinksInfolistSection;
use Filament\Support\Enums\IconPosition;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Component;

class LegacyLinksRelationManager extends Component
{
    use HasLegacyLinksInfolistSection;

    #[Locked]
    public Model $ownerRecord;

    #[Locked]
    public ?string $pageClass = null;

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Legacy links';
    }

    public static function getIcon(Model $ownerRecord, string $pageClass): ?string
    {
        return null;
    }

    public static function getIconPosition(Model $ownerRecord, string $pageClass): IconPosition
    {
        return IconPosition::Before;
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return null;
    }

    public static function getBadgeColor(Model $ownerRecord, string $pageClass): ?string
    {
        return null;
    }

    public static function getBadgeTooltip(Model $ownerRecord, string $pageClass): ?string
    {
        return null;
    }

    public static function getDefaultProperties(): array
    {
        return [];
    }

    public function render(): View
    {
        return view('filament.resources.relation-managers.legacy-links-relation-manager', [
            'legacyLinks' => static::legacyLinksHtml($this->ownerRecord),
        ]);
    }
}
