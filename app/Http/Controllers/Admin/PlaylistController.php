<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaItem;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PlaylistController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Playlists/Index', [
            'playlists' => Playlist::latest()->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Playlists/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $playlist = Playlist::create($request->only('name'));

        // إعادة التوجيه لصفحة التعديل مباشرة لإضافة الوسائط
        return redirect()->route('admin.playlists.edit', $playlist)->with('success', 'تم إنشاء القائمة بنجاح. يمكنك الآن إضافة الوسائط.');
    }

    public function edit(Playlist $playlist)
    {
        // جلب معرفات الوسائط الموجودة حاليًا في القائمة
        $playlistMediaIds = $playlist->mediaItems()->pluck('media_item_id')->toArray();

        return Inertia::render('Admin/Playlists/Edit', [
            'playlist' => $playlist,
            'mediaItems' => MediaItem::latest()->get()->map(function ($item) {
                $item->url = Storage::url($item->file_path);
                return $item;
            }),
            'playlistMediaIds' => $playlistMediaIds,
        ]);
    }

    public function update(Request $request, Playlist $playlist)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'media_items' => ['sometimes', 'array'],
            'media_items.*' => ['integer', 'exists:media_items,id'],
        ]);

        $playlist->update($request->only('name'));

        // استخدام sync لتحديث الوسائط المرتبطة بالقائمة
        $playlist->mediaItems()->sync($request->input('media_items', []));

        return redirect()->route('admin.playlists.index')->with('success', 'تم تحديث القائمة بنجاح.');
    }

    public function destroy(Playlist $playlist)
    {
        $playlist->delete();

        return redirect()->route('admin.playlists.index')->with('success', 'تم حذف القائمة بنجاح.');
    }
}
