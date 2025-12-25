<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MediaItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class BroadcastController extends Controller
{
    /**
     * Get current broadcast status
     * 
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
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
                'is_active' => $activeBroadcastId !== null,
                'broadcast_item' => $broadcastItem,
            ],
        ]);
    }
}

