<?php

namespace App\Filament\Resources\TimelineEventResource\RelationManagers;

use App\Filament\Resources\RelationManagers\BaseImagesRelationManager;
use App\Models\TimelineEventImage;

class ImagesRelationManager extends BaseImagesRelationManager
{
    protected static string $relationship = 'images';

    protected function imageModelClass(): string
    {
        return TimelineEventImage::class;
    }

    protected function ownerForeignKey(): string
    {
        return 'timeline_event_id';
    }

    protected function ownerRouteParameter(): string
    {
        return 'timelineEvent';
    }

    protected function imageRouteParameter(): string
    {
        return 'timelineEventImage';
    }

    protected function imageViewRouteName(): string
    {
        return 'filament.admin.timeline-event-image.view';
    }

    protected function imageDownloadRouteName(): string
    {
        return 'filament.admin.timeline-event-image.download';
    }
}
