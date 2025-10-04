<?php

namespace App\Http\Controllers\Admin;

use App\Events\BroadcastMedia;
use App\Events\StopBroadcast;
use App\Http\Controllers\Controller;
use App\Models\MediaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class BroadcastController extends Controller
{
    public function index()
    {
        // جلب الوسائط المرئية فقط
        $visualMedia = MediaItem::whereIn('file_type', ['image', 'video'])
            ->latest()
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'url' => Storage::url($item->file_path),
                'type' => $item->file_type,
            ]);

        // التحقق من وجود بث نشط حاليًا
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

        Log::info('--- BROADCAST: Attempting to dispatch event now. ---'); // 2. أضف هذا السطر

        // بث الحدث لجميع الشاشات
        BroadcastMedia::dispatch($mediaItem);

        Log::info('--- BROADCAST: Event dispatch command has been executed. ---'); // 3. أضف هذا السطر

        Cache::put('active_broadcast_media_id', $mediaItem->id);

        return back()->with('success', 'تم بدء البث العام بنجاح.');
    }

    public function destroy()
    {
        // إرسال حدث إيقاف البث
        StopBroadcast::dispatch();

        // إزالة حالة البث من الكاش
        Cache::forget('active_broadcast_media_id');

        return back()->with('success', 'تم إيقاف البث العام.');
    }
}
