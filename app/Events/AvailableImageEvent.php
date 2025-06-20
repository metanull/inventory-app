<?php

namespace App\Events;

use App\Models\AvailableImage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class AvailableImageEvent
 *
 * This event is dispatched following a successful image upload.
 * It carries the AvailableImage model instance that was created.
 * The image associated with this event was uploaded by the user, validated, and stored in the local storage disk.
 * The image was also resized if it exceeded the maximum dimensions defined in the configuration.
 */
class AvailableImageEvent
{
    public $availableImage;

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(AvailableImage $availableImage)
    {
        $this->availableImage = $availableImage;
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
