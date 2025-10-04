<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MediaItemController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/MediaItems/Index', [
            'mediaItems' => MediaItem::latest()->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => $item->file_type,
                    'url' => Storage::url($item->file_path),
                ];
            }),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/MediaItems/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'files' => 'required|array',
            // **تحديث**: إضافة أنواع الملفات الصوتية
            'files.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi,mp3,wav|max:51200',
        ]);

        foreach ($validated['files'] as $file) {
            $path = $file->store('media', 'public');

            // **تحديث**: منطق محسن لتحديد نوع الملف
            $mime = $file->getMimeType();
            if (str_starts_with($mime, 'image')) {
                $type = 'image';
            } elseif (str_starts_with($mime, 'video')) {
                $type = 'video';
            } else {
                $type = 'audio';
            }

            MediaItem::create([
                'file_path' => $path,
                'file_type' => $type,
                'duration' => 10,
            ]);
        }

        return redirect()->route('admin.media-items.index')->with('success', 'تم رفع الملفات بنجاح.');
    }


    public function destroy(MediaItem $mediaItem)
    {
        Storage::disk('public')->delete($mediaItem->file_path);
        $mediaItem->delete();

        return redirect()->route('admin.media-items.index')->with('success', 'تم حذف الملف بنجاح.');
    }
}

