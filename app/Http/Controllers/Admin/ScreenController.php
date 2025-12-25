<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaItem;
use App\Models\Playlist;
use App\Models\Screen;
use App\Models\ScreenAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Events\ScreenContentUpdated;
use Inertia\Response;

class ScreenController extends Controller
{
    public function index(): Response
    {
        $screens = Screen::with('assignment.assignable', 'backgroundAudio')
            ->latest()
            ->get()
            ->map(function ($screen) {
                $contentInfo = null;
                if ($screen->assignment && $screen->assignment->assignable) {
                    if ($screen->assignment->assignable_type === Playlist::class) {
                        $playlist = $screen->assignment->assignable;
                        $contentInfo = [
                            'type' => 'playlist',
                            'name' => $playlist->name,
                            'media_count' => $playlist->mediaItems()->count(),
                        ];
                    } elseif ($screen->assignment->assignable_type === MediaItem::class) {
                        $mediaItem = $screen->assignment->assignable;
                        $contentInfo = [
                            'type' => 'media',
                            'name' => basename($mediaItem->file_path),
                            'file_type' => $mediaItem->file_type,
                        ];
                    }
                }

                return [
                    'id' => $screen->id,
                    'name' => $screen->name,
                    'screen_code' => $screen->screen_code,
                    'orientation' => $screen->orientation,
                    'resolution' => $screen->resolution,
                    'is_active' => $screen->is_active,
                    'content' => $contentInfo,
                    'has_background_audio' => $screen->backgroundAudio !== null,
                    'created_at' => $screen->created_at,
                ];
            });

        return Inertia::render('Admin/Screens/Index', [
            'screens' => $screens,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Screens/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'screen_code' => 'required|string|max:255|unique:screens,screen_code',
            'orientation' => 'required|in:landscape,portrait',
        ]);

        Screen::create($validated);

        return redirect()->route('admin.screens.index')->with('success', 'تم إنشاء الشاشة بنجاح.');
    }

    public function edit(Screen $screen): Response
    {
        // تحميل العلاقات الحالية
        $screen->load('assignment', 'backgroundAudio');

        // تنسيق الوسائط المرئية لشبكة الاختيار
        $visualMedia = MediaItem::whereIn('file_type', ['image', 'video'])
            ->latest()
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'url' => Storage::url($item->file_path),
                'type' => $item->file_type,
            ]);

        // تنسيق الوسائط الصوتية لشبكة الاختيار
        $audioMedia = MediaItem::where('file_type', 'audio')
            ->latest()
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'file_name' => basename($item->file_path),
            ]);

        return Inertia::render('Admin/Screens/Edit', [
            'screen' => $screen,
            'playlists' => Playlist::all(['id', 'name']),
            'visualMediaItems' => $visualMedia,
            'backgroundAudios' => $audioMedia,
        ]);
    }

    public function update(Request $request, Screen $screen)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'screen_code' => 'required|string|max:255|unique:screens,screen_code,' . $screen->id,
            'orientation' => 'required|in:landscape,portrait',
            'resolution' => 'nullable|string|regex:/^\d+x\d+$/',
            'assignable_type' => 'nullable|string|in:App\Models\Playlist,App\Models\MediaItem',
            'assignable_id' => 'nullable|integer',
            'background_audio_id' => 'nullable|integer|exists:media_items,id',
        ]);

        // تحديث تفاصيل الشاشة الأساسية
        $screen->update([
            'name' => $validated['name'],
            'screen_code' => $validated['screen_code'],
            'orientation' => $validated['orientation'],
            'resolution' => $validated['resolution'],
            'background_audio_id' => $validated['background_audio_id'],
        ]);

        // تحديث تخصيص المحتوى
        if (!empty($validated['assignable_type']) && !empty($validated['assignable_id'])) {
            ScreenAssignment::updateOrCreate(
                ['screen_id' => $screen->id],
                [
                    'assignable_type' => $validated['assignable_type'],
                    'assignable_id' => $validated['assignable_id'],
                ]
            );
        } else {
            // حذف التخصيص إذا لم يتم اختيار أي محتوى
            $screen->assignment()->delete();
        }

          // **جديد**: إرسال حدث التحديث إلى الشاشة المعنية
        // نقوم بإعادة تحميل الشاشة لضمان الحصول على أحدث البيانات
        $screen->refresh();
        ScreenContentUpdated::dispatch($screen);
        return redirect()->route('admin.screens.index')->with('success', 'تم تحديث الشاشة بنجاح.');
    }


    public function destroy(Screen $screen)
    {
        $screen->assignment()->delete();
        $screen->delete();
        return redirect()->route('admin.screens.index')->with('success', 'تم حذف الشاشة بنجاح.');
    }
}

