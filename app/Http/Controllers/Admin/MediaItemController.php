<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaItem;
use App\Services\VideoOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

class MediaItemController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/MediaItems/Index', [
            'mediaItems' => MediaItem::latest('id')->get()->map(function ($item) {
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
        // زيادة وقت التنفيذ للسماح بمعالجة الفيديوهات الكبيرة
        set_time_limit(3600); // 60 دقيقة
        ini_set('max_execution_time', '3600');

        $validated = $request->validate([
            'files' => 'required|array',
            // **تحديث**: إضافة أنواع الملفات الصوتية
            'files.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi,mp3,wav|max:51200',
        ]);

        $videoOptimizer = new VideoOptimizer();
        $hasFFmpeg = $videoOptimizer->isFFmpegAvailable();

        Log::info('Media upload started', [
            'ffmpeg_available' => $hasFFmpeg,
            'files_count' => count($validated['files']),
        ]);

        $optimizedCount = 0;
        $skippedCount = 0;

        foreach ($validated['files'] as $file) {
            $mime = $file->getMimeType();

            // تحديد نوع الملف
            if (str_starts_with($mime, 'image')) {
                $type = 'image';
                $path = $file->store('media', 'public');
                $duration = 10;
            } elseif (str_starts_with($mime, 'video')) {
                $type = 'video';

                // إذا كان FFmpeg متاحاً، حاول تحسين الفيديو
                if ($hasFFmpeg) {
                    Log::info('FFmpeg available, attempting to optimize video', [
                        'file' => $file->getClientOriginalName(),
                        'mime' => $mime,
                    ]);

                    // حفظ الملف الأصلي مؤقتاً
                    $tempPath = $file->store('temp', 'public');
                    $tempFullPath = Storage::disk('public')->path($tempPath);

                    Log::info('Original file stored temporarily', [
                        'temp_path' => $tempPath,
                        'temp_full_path' => $tempFullPath,
                        'file_exists' => file_exists($tempFullPath),
                    ]);

                    // محاولة تحسين الفيديو
                    $optimizedPath = 'media/' . uniqid() . '_optimized.mp4';
                    $optimizedFullPath = Storage::disk('public')->path($optimizedPath);

                    Log::info('Starting optimization', [
                        'optimized_path' => $optimizedPath,
                        'optimized_full_path' => $optimizedFullPath,
                    ]);

                    $optimized = $videoOptimizer->optimize($tempFullPath, $optimizedFullPath);

                    if ($optimized && file_exists($optimizedFullPath)) {
                        // استخدام الملف المحسّن
                        $path = $optimizedPath;
                        $optimizedCount++;

                        Log::info('Video optimized successfully', [
                            'original_size' => filesize($tempFullPath),
                            'optimized_size' => filesize($optimizedFullPath),
                            'path' => $path,
                        ]);

                        // حذف الملف المؤقت
                        Storage::disk('public')->delete($tempPath);

                        // الحصول على مدة الفيديو
                        $duration = $videoOptimizer->getDuration($optimizedFullPath) ?? 10;
                    } else {
                        // في حالة فشل التحسين، استخدام الملف الأصلي
                        $path = $tempPath;
                        $skippedCount++;
                        Log::warning('Video optimization failed, using original file', [
                            'file' => $file->getClientOriginalName(),
                            'path' => $tempPath,
                            'optimized_path' => $optimizedPath,
                            'optimized_exists' => file_exists($optimizedFullPath),
                        ]);

                        // الحصول على مدة الفيديو من الملف الأصلي
                        $duration = $videoOptimizer->getDuration($tempFullPath) ?? 10;
                    }
                } else {
                    // بدون FFmpeg، استخدم الملف الأصلي مباشرة
                    Log::warning('FFmpeg not available, using original file without optimization', [
                        'file' => $file->getClientOriginalName(),
                    ]);
                    $path = $file->store('media', 'public');
                    $duration = 10; // القيمة الافتراضية
                    $skippedCount++;
                }
            } else {
                $type = 'audio';
                $path = $file->store('media', 'public');
                $duration = 10;
            }

            MediaItem::create([
                'file_path' => $path,
                'file_type' => $type,
                'duration' => $duration,
            ]);
        }

        $message = 'تم رفع الملفات بنجاح.';
        if ($optimizedCount > 0) {
            $message .= " تم تحسين {$optimizedCount} فيديو باستخدام FFmpeg.";
        }
        if ($skippedCount > 0 && $hasFFmpeg) {
            $message .= " تم تخطي تحسين {$skippedCount} فيديو (حدث خطأ أثناء التحسين).";
        } elseif ($skippedCount > 0 && !$hasFFmpeg) {
            $message .= " تم رفع {$skippedCount} فيديو بدون تحسين (FFmpeg غير متاح - راجع ملف FFMPEG_INSTALLATION.md للتثبيت).";
        }

        return redirect()->route('admin.media-items.index')->with('success', $message);
    }


    public function destroy(MediaItem $mediaItem)
    {
        Storage::disk('public')->delete($mediaItem->file_path);
        $mediaItem->delete();

        return redirect()->route('admin.media-items.index')->with('success', 'تم حذف الملف بنجاح.');
    }

    /**
     * حذف عدة ملفات دفعة واحدة
     */
    public function destroyMultiple(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:media_items,id',
        ]);

        $deletedCount = 0;
        $failedCount = 0;

        foreach ($validated['ids'] as $id) {
            try {
                $mediaItem = MediaItem::find($id);
                if ($mediaItem) {
                    Storage::disk('public')->delete($mediaItem->file_path);
                    $mediaItem->delete();
                    $deletedCount++;
                }
            } catch (\Exception $e) {
                Log::error('Failed to delete media item', [
                    'id' => $id,
                    'error' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        $message = "تم حذف {$deletedCount} ملف بنجاح.";
        if ($failedCount > 0) {
            $message .= " فشل حذف {$failedCount} ملف.";
        }

        return redirect()->route('admin.media-items.index')->with('success', $message);
    }

    /**
     * مسح مجلد الوسائط وإضافة الملفات الجديدة
     */
    public function scan()
    {
        try {
            Artisan::call('media:scan');
            $output = Artisan::output();

            return redirect()->route('admin.media-items.index')->with('success', 'تم مسح المجلد وإضافة الملفات الجديدة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->route('admin.media-items.index')->with('error', 'حدث خطأ أثناء مسح المجلد: ' . $e->getMessage());
        }
    }
}

