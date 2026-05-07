<?php

namespace App\Filament\Widgets;

use App\Enums\Permission;
use App\Models\AvailableImage;
use App\Models\ImageUpload;
use App\Support\Images\AttachedImageRegistry;
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
        $total = 0;

        foreach (AttachedImageRegistry::modelClasses() as $class) {
            $total += (int) $class::query()->sum('size');
        }

        return $total;
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
