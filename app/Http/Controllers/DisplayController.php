<?php

namespace App\Http\Controllers;

use App\Models\Department;
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
        return Inertia::render('Display/Show', [
            'screen' => [
                'name' => $screen->name,
                'orientation' => $screen->orientation,
                'resolution' => $screen->resolution,
                'screen_code' => $screen->screen_code, // **تحديث هنا**
            ],
            'mediaItems' => $formattedMedia,
            'departments' => $departments,
            'backgroundAudioUrl' => $backgroundAudioUrl,
            'initialBroadcastItem' => $initialBroadcastItem,
        ]);
    }
}

