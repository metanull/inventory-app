<?php

namespace App\Filament\Widgets;

use App\Enums\Permission;
use App\Models\AvailableImage;
use App\Models\CollectionImage;
use App\Models\ContributorImage;
use App\Models\ImageUpload;
use App\Models\ItemImage;
use App\Models\PartnerImage;
use App\Models\PartnerLogo;
use App\Models\PartnerTranslationImage;
use App\Models\TimelineEventImage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StorageUsageWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::VIEW_DATA->value) ?? false;
    }

    protected function getStats(): array
    {
        $managedBytes = $this->getManagedStorageBytes();
        $availablePoolBytes = $this->getAvailablePoolBytes();
        $pendingBytes = $this->getPendingUploadBytes();

        return [
            Stat::make('Managed Image Storage', self::formatBytes($managedBytes))
                ->description('Total across all attached image tables')
                ->icon('heroicon-o-photo'),
            Stat::make('Available Image Pool', self::formatBytes($availablePoolBytes))
                ->description('Unattached images awaiting use')
                ->icon('heroicon-o-archive-box'),
            Stat::make('Pending Uploads', self::formatBytes($pendingBytes))
                ->description('Private uploads awaiting processing')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning'),
        ];
    }

    public function getManagedStorageBytes(): int
    {
        return (int) (
            ItemImage::query()->sum('size') +
            CollectionImage::query()->sum('size') +
            PartnerImage::query()->sum('size') +
            PartnerLogo::query()->sum('size') +
            PartnerTranslationImage::query()->sum('size') +
            ContributorImage::query()->sum('size') +
            TimelineEventImage::query()->sum('size')
        );
    }

    public function getAvailablePoolBytes(): int
    {
        return (int) AvailableImage::query()->sum('size');
    }

    public function getPendingUploadBytes(): int
    {
        return (int) ImageUpload::query()->sum('size');
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_073_741_824) {
            return number_format($bytes / 1_073_741_824, 2).' GB';
        }

        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 2).' MB';
        }

        if ($bytes >= 1_024) {
            return number_format($bytes / 1_024, 2).' KB';
        }

        return $bytes.' B';
    }
}
