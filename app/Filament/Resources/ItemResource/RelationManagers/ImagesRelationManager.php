<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

use App\Filament\Resources\RelationManagers\BaseImagesRelationManager;
use App\Models\ItemImage;

class ImagesRelationManager extends BaseImagesRelationManager
{
    protected static string $relationship = 'itemImages';

    protected function imageModelClass(): string
    {
        return ItemImage::class;
    }

    protected function ownerForeignKey(): string
    {
        return 'item_id';
    }

    protected function ownerRouteParameter(): string
    {
        return 'item';
    }

    protected function imageRouteParameter(): string
    {
        return 'itemImage';
    }

    protected function imageViewRouteName(): string
    {
        return 'filament.admin.item-image.view';
    }

    protected function imageDownloadRouteName(): string
    {
        return 'filament.admin.item-image.download';
    }
}
