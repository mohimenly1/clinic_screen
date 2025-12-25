<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaItemResource;
use App\Models\MediaItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class MediaController extends Controller
{
    /**
     * Get media item download URL
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $mediaItem = MediaItem::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new MediaItemResource($mediaItem),
        ]);
    }
}

