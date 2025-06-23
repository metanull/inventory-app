<?php

namespace App\Events;

use App\Models\ImageUpload;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class ImageUploadEvent
 *
 * This event is dispatched when an image upload occurs.
 * It carries the ImageUpload model instance.
 *
 * The image associated with this event was just uploaded by the user, and was not yet validated
 * (proceed with caution).
 */
class ImageUploadEvent
{
    public $imageUpload;

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(ImageUpload $imageUpload)
    {
        $this->imageUpload = $imageUpload;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
