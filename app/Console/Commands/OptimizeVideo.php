<?php

namespace App\Console\Commands;

use App\Models\MediaItem;
use App\Services\VideoOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class OptimizeVideo extends Command
{
    protected $signature = 'video:optimize {id? : Media Item ID} {--all : Optimize all videos}';
    protected $description = 'تحسين فيديو محدد أو جميع الفيديوهات';

    public function handle()
    {
        $videoOptimizer = new VideoOptimizer();
        
        if (!$videoOptimizer->isFFmpegAvailable()) {
            $this->error('FFmpeg غير متاح في النظام!');
            $this->info('راجع ملف FFMPEG_INSTALLATION.md لتعليمات التثبيت.');
            return 1;
        }
        
        $this->info('FFmpeg متاح ✓');

        if ($this->option('all')) {
            $videos = MediaItem::where('file_type', 'video')->get();
            $this->info("تم العثور على {$videos->count()} فيديو");
            
            foreach ($videos as $video) {
                $this->optimizeVideo($video, $videoOptimizer);
            }
        } elseif ($this->argument('id')) {
            $video = MediaItem::find($this->argument('id'));
            if (!$video || $video->file_type !== 'video') {
                $this->error('فيديو غير موجود أو الملف ليس فيديو!');
                return 1;
            }
            $this->optimizeVideo($video, $videoOptimizer);
        } else {
            $this->error('يجب تحديد ID الفيديو أو استخدام --all');
            $this->info('مثال: php artisan video:optimize 1');
            $this->info('أو: php artisan video:optimize --all');
            return 1;
        }

        return 0;
    }

    private function optimizeVideo(MediaItem $video, VideoOptimizer $optimizer)
    {
        $this->info("معالجة الفيديو ID: {$video->id}");
        $this->info("المسار الحالي: {$video->file_path}");
        
        $currentPath = Storage::disk('public')->path($video->file_path);
        
        if (!file_exists($currentPath)) {
            $this->error("الملف غير موجود: {$currentPath}");
            return;
        }
        
        // تخطي إذا كان الملف محسّناً بالفعل
        if (str_contains($video->file_path, '_optimized')) {
            $this->warn("الفيديو محسّن بالفعل، تم التخطي");
            return;
        }
        
        $this->info("حجم الملف الأصلي: " . $this->formatBytes(filesize($currentPath)));
        
        // إنشاء مسار للملف المحسّن
        $optimizedPath = 'media/' . uniqid() . '_optimized.mp4';
        $optimizedFullPath = Storage::disk('public')->path($optimizedPath);
        
        $this->info("بدء التحسين...");
        $result = $optimizer->optimize($currentPath, $optimizedFullPath);
        
        if ($result && file_exists($optimizedFullPath)) {
            $this->info("✓ تم التحسين بنجاح!");
            $this->info("حجم الملف المحسّن: " . $this->formatBytes(filesize($optimizedFullPath)));
            
            // تحديث قاعدة البيانات
            $oldPath = $video->file_path;
            $video->file_path = $optimizedPath;
            $video->duration = $optimizer->getDuration($optimizedFullPath) ?? $video->duration;
            $video->save();
            
            // حذف الملف القديم
            Storage::disk('public')->delete($oldPath);
            $this->info("تم حذف الملف القديم: {$oldPath}");
        } else {
            $this->error("✗ فشل التحسين");
            $this->info("راجع ملف storage/logs/laravel.log للتفاصيل");
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
