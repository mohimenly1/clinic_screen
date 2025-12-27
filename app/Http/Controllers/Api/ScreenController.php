<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaItemResource;
use App\Models\Screen;
use App\Models\MediaItem;
use App\Models\Playlist;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
        Log::info("API: Screen data requested for code: {$code}", [
            'code' => $code,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        
        try {
            $screen = Screen::with('assignment', 'backgroundAudio')
                ->where('screen_code', $code)
                ->where('is_active', true)
                ->firstOrFail();
            
            Log::info("API: Screen found", [
                'screen_id' => $screen->id,
                'screen_name' => $screen->name,
                'code' => $code,
            ]);

            $mediaItems = collect();
            if ($assignment = $screen->assignment) {
                if ($assignment->assignable_type === Playlist::class && $assignment->assignable) {
                    $mediaItems = $assignment->assignable->mediaItems()->orderBy('pivot_order')->get();
                } elseif ($assignment->assignable_type === MediaItem::class && $assignment->assignable) {
                    $mediaItems->push($assignment->assignable);
                }
            }

            $formattedMedia = MediaItemResource::collection($mediaItems);

            Log::info("API: Media items formatted", [
                'count' => $formattedMedia->count(),
                'code' => $code,
            ]);

            $backgroundAudioUrl = null;
            if ($screen->backgroundAudio) {
                $url = Storage::url($screen->backgroundAudio->file_path);
                if (!str_starts_with($url, 'http')) {
                    $backgroundAudioUrl = request()->schemeAndHttpHost() . $url;
                } else {
                    $backgroundAudioUrl = $url;
                }
            }

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

            $response = response()->json([
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
            
            Log::info("API: Screen data response sent successfully", [
                'code' => $code,
                'media_count' => $formattedMedia->count(),
            ]);
            
            return $response;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning("API: Screen not found", [
                'code' => $code,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Screen not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error("API: Error loading screen data", [
                'code' => $code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }
}
