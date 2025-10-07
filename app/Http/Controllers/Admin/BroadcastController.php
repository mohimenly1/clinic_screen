<?php

namespace App\Http\Controllers\Admin;

use App\Events\BroadcastMedia;
use App\Events\StopBroadcast;
use App\Http\Controllers\Controller;
use App\Models\MediaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class BroadcastController extends Controller
{
    public function index()
    {
        // ... (هذا الجزء يبقى كما هو)
        $visualMedia = MediaItem::whereIn('file_type', ['image', 'video'])
            ->latest()
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'url' => \Illuminate\Support\Facades\Storage::url($item->file_path),
                'type' => $item->file_type,
            ]);
        $activeBroadcastId = Cache::get('active_broadcast_media_id');
        return Inertia::render('Admin/Broadcast/Index', [
            'visualMediaItems' => $visualMedia,
            'activeBroadcastId' => $activeBroadcastId,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['media_item_id' => 'required|exists:media_items,id']);
        $mediaItem = MediaItem::find($request->media_item_id);

        try {
            Log::info('--- BROADCAST: Creating new BroadcastMedia event instance. ---');
            $event = new BroadcastMedia($mediaItem);
            Log::info('--- BROADCAST: Calling the broadcast() helper function. ---');

            // **تحديث**: استخدام دالة broadcast() المباشرة
            broadcast($event);

            Log::info('--- BROADCAST: broadcast() helper function executed without errors. ---');
        } catch (\Exception $e) {
            Log::error('--- BROADCAST FAILED ---: ' . $e->getMessage());
            // في حالة الفشل، سيتم تسجيل الخطأ، ويمكنك إظهار رسالة خطأ للمستخدم
            return back()->with('error', 'فشل بدء البث: ' . $e->getMessage());
        }

        Cache::put('active_broadcast_media_id', $mediaItem->id);
        return back()->with('success', 'تم بدء البث العام بنجاح.');
    }

    public function destroy()
    {
        try {
            Log::info('--- BROADCAST: Calling broadcast() for StopBroadcast. ---');

            // **تحديث**: استخدام دالة broadcast() المباشرة
            broadcast(new StopBroadcast());

            Log::info('--- BROADCAST: StopBroadcast executed without errors. ---');
        } catch (\Exception $e) {
            Log::error('--- BROADCAST STOP FAILED ---: ' . $e->getMessage());
            return back()->with('error', 'فشل إيقاف البث: ' . $e->getMessage());
        }

        Cache::forget('active_broadcast_media_id');
        return back()->with('success', 'تم إيقاف البث العام.');
    }
}

