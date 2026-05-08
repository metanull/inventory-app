<?php

namespace App\Filament\Resources\PartnerResource\RelationManagers;

use App\Filament\Resources\RelationManagers\BaseImagesRelationManager;
use App\Models\PartnerImage;

class ImagesRelationManager extends BaseImagesRelationManager
{
    protected static string $relationship = 'partnerImages';

    protected function imageModelClass(): string
    {
        return PartnerImage::class;
    }

    protected function ownerForeignKey(): string
    {
        return 'partner_id';
    }

    protected function ownerRouteParameter(): string
    {
        return 'partner';
    }

    protected function imageRouteParameter(): string
    {
        return 'partnerImage';
    }

    protected function imageViewRouteName(): string
    {
        return 'filament.admin.partner-image.view';
    }

    protected function imageDownloadRouteName(): string
    {
        return 'filament.admin.partner-image.download';
    }
}
