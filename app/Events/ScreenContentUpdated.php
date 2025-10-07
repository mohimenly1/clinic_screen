<?php

namespace App\Events;

use App\Models\Screen;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ScreenContentUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $mediaItems;
    protected Screen $screen;

    public function __construct(Screen $screen)
    {
        $this->screen = $screen;

        // **تصحيح**: استخدام نفس المنطق الآمن الموجود في وحدة تحكم العرض
        $mediaItemsCollection = collect();
        $assignment = $screen->assignment; // تحميل العلاقة

        if ($assignment && $assignment->assignable) {
            if ($assignment->assignable_type === \App\Models\Playlist::class) {
                $mediaItemsCollection = $assignment->assignable->mediaItems()->orderBy('pivot_order')->get();
            } elseif ($assignment->assignable_type === \App\Models\MediaItem::class) {
                $mediaItemsCollection->push($assignment->assignable);
            }
        }

        $this->mediaItems = $mediaItemsCollection->map(fn($item) => [
            'id' => $item->id,
            'url' => Storage::url($item->file_path),
            'type' => $item->file_type,
            'duration' => $item->duration,
        ])->toArray();
    }

    public function broadcastOn(): array
    {
        return [new Channel('displays.' . $this->screen->screen_code)];
    }

    public function broadcastAs(): string
    {
        return 'ScreenContentUpdated';
    }
}

