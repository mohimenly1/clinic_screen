<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaItemResource;
use App\Models\Screen;
use App\Models\MediaItem;
use App\Models\Playlist;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScreenController extends Controller
{
    /**
     * Get screen data by code
     * 
     * @param string $code
     * @return JsonResponse
     */
    public function show(string $code): JsonResponse
    {
        $screen = Screen::with('assignment', 'backgroundAudio')
            ->where('screen_code', $code)
            ->where('is_active', true)
            ->firstOrFail();

        $mediaItems = collect();
        if ($assignment = $screen->assignment) {
            if ($assignment->assignable_type === Playlist::class && $assignment->assignable) {
                $mediaItems = $assignment->assignable->mediaItems()->orderBy('pivot_order')->get();
            } elseif ($assignment->assignable_type === MediaItem::class && $assignment->assignable) {
                $mediaItems->push($assignment->assignable);
            }
        }

        $formattedMedia = MediaItemResource::collection($mediaItems);

        $backgroundAudioUrl = $screen->backgroundAudio ? Storage::url($screen->backgroundAudio->file_path) : null;
        
        // Check for active broadcast
        $activeBroadcastId = Cache::get('active_broadcast_media_id');
        $broadcastItem = null;
        if ($activeBroadcastId) {
            $mediaItem = MediaItem::find($activeBroadcastId);
            if ($mediaItem) {
                $broadcastItem = [
                    'id' => $mediaItem->id,
                    'url' => Storage::url($mediaItem->file_path),
                    'type' => $mediaItem->file_type,
                    'file_name' => basename($mediaItem->file_path),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'screen' => [
                    'id' => $screen->id,
                    'name' => $screen->name,
                    'code' => $screen->screen_code,
                    'orientation' => $screen->orientation,
                    'resolution' => $screen->resolution,
                ],
                'media_items' => $formattedMedia->collection->all(),
                'background_audio_url' => $backgroundAudioUrl,
                'broadcast_item' => $broadcastItem,
            ],
        ]);
    }
}

