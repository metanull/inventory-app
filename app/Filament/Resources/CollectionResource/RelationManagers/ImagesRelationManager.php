<?php

namespace App\Filament\Resources\CollectionResource\RelationManagers;

use App\Filament\Resources\RelationManagers\BaseImagesRelationManager;
use App\Models\CollectionImage;

class ImagesRelationManager extends BaseImagesRelationManager
{
    protected static string $relationship = 'collectionImages';

    protected function imageModelClass(): string
    {
        return CollectionImage::class;
    }

    protected function ownerForeignKey(): string
    {
        return 'collection_id';
    }

    protected function ownerRouteParameter(): string
    {
        return 'collection';
    }

    protected function imageRouteParameter(): string
    {
        return 'collectionImage';
    }

    protected function imageViewRouteName(): string
    {
        return 'filament.admin.collection-image.view';
    }

    protected function imageDownloadRouteName(): string
    {
        return 'filament.admin.collection-image.download';
    }
}
