<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Floor;
use App\Models\MediaItem;
use App\Models\Playlist;
use App\Models\Screen;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DisplayController extends Controller
{
    public function show($code): Response
    {
        Inertia::setRootView('display');
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
        $formattedMedia = $mediaItems->map(fn($item) => [
            'id' => $item->id,
            'url' => Storage::url($item->file_path),
            'type' => $item->file_type,
            'duration' => $item->duration,
        ]);
        $departments = Department::with(['doctors.schedules'])->orderBy('name')->get()->map(fn($department) => [
            'id' => $department->id,
            'name' => $department->name,
            'doctors' => $department->doctors->map(fn($doctor) => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'photo_url' => $doctor->photo_path ? Storage::url($doctor->photo_path) : null,
                'schedules' => $doctor->schedules,
            ]),
        ]);
        $backgroundAudioUrl = $screen->backgroundAudio ? Storage::url($screen->backgroundAudio->file_path) : null;
        $initialBroadcastItem = null;
        $activeBroadcastId = Cache::get('active_broadcast_media_id');
        if ($activeBroadcastId) {
            $mediaItem = MediaItem::find($activeBroadcastId);
            if ($mediaItem) {
                $initialBroadcastItem = [
                    'url' => Storage::url($mediaItem->file_path),
                    'type' => $mediaItem->file_type,
                ];
            }
        }

        // Load floors with rooms and their images for AR navigation
        $floors = Floor::with(['rooms' => function($query) {
            $query->where('is_active', true)
                ->with(['images' => function($imgQuery) {
                    $imgQuery->where('is_active', true)->orderBy('display_order');
                }])
                ->orderBy('room_number');
        }])
            ->orderBy('display_order')
            ->orderBy('floor_number')
            ->get()
            ->map(function($floor) {
                return [
                    'id' => $floor->id,
                    'name' => $floor->name,
                    'floor_number' => $floor->floor_number ?? 0,
                    'map_image_url' => $floor->map_image_url,
                    'description' => $floor->description,
                    'rooms' => $floor->rooms->map(function($room) {
                        return [
                            'id' => $room->id,
                            'name' => $room->name,
                            'room_number' => $room->room_number,
                            'room_type' => $room->room_type,
                            'map_x' => $room->map_x,
                            'map_y' => $room->map_y,
                            'color' => $room->color ?? $room->getColorAttribute(),
                            'icon' => $room->icon ?? $room->getIconAttribute(),
                            'description' => $room->description,
                            'images' => $room->images->map(function($image) {
                                return [
                                    'id' => $image->id,
                                    'image_url' => Storage::url($image->image_path),
                                    'description' => $image->description,
                                    'ar_instructions' => $image->ar_instructions,
                                    'display_order' => $image->display_order,
                                ];
                            }),
                        ];
                    }),
                ];
            });

        return Inertia::render('Display/Show', [
            'screen' => [
                'name' => $screen->name,
                'orientation' => $screen->orientation,
                'resolution' => $screen->resolution,
                'screen_code' => $screen->screen_code,
            ],
            'mediaItems' => $formattedMedia,
            'departments' => $departments,
            'backgroundAudioUrl' => $backgroundAudioUrl,
            'initialBroadcastItem' => $initialBroadcastItem,
            'floors' => $floors,
        ]);
    }
}

