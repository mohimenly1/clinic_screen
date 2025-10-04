<?php

namespace App\Events;

use App\Models\MediaItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BroadcastMedia implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $mediaItem;

    public function __construct(MediaItem $mediaItem)
    {
        $this->mediaItem = [
            'url' => Storage::url($mediaItem->file_path),
            'type' => $mediaItem->file_type,
        ];
    }

    public function broadcastOn(): array
    {
        return [new Channel('displays')];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'BroadcastMedia';
    }
}

