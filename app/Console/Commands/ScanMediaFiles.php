<?php

namespace App\Console\Commands;

use App\Models\MediaItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ScanMediaFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:scan {--dry-run : Show what would be added without actually adding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan the media directory and add new files to the media library';

    /**
     * Allowed file extensions
     */
    protected array $allowedExtensions = [
        'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'videos' => ['mp4', 'mov', 'avi', 'mkv', 'webm'],
        'audio' => ['mp3', 'wav', 'ogg', 'm4a'],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('🔍 Scanning media directory...');
        
        $mediaPath = Storage::disk('public')->path('media');
        
        // إنشاء المجلد إذا لم يكن موجوداً
        if (!File::exists($mediaPath)) {
            File::makeDirectory($mediaPath, 0755, true);
            $this->info("📁 Created media directory: {$mediaPath}");
        }

        // الحصول على جميع الملفات في المجلد
        $files = File::allFiles($mediaPath);
        
        if (empty($files)) {
            $this->warn('⚠️  No files found in media directory.');
            return Command::SUCCESS;
        }

        $this->info("📋 Found " . count($files) . " file(s) to check.");
        
        $added = 0;
        $skipped = 0;
        $errors = 0;

        // الحصول على جميع مسارات الملفات الموجودة في قاعدة البيانات
        $existingPaths = MediaItem::pluck('file_path')->toArray();

        foreach ($files as $file) {
            $relativePath = 'media/' . $file->getRelativePathname();
            
            // تخطي الملفات المخفية والمجلدات
            if ($file->getFilename()[0] === '.' || $file->isDir()) {
                continue;
            }

            // التحقق من أن الملف غير موجود في قاعدة البيانات
            if (in_array($relativePath, $existingPaths)) {
                $skipped++;
                continue;
            }

            // تحديد نوع الملف
            $extension = strtolower($file->getExtension());
            $type = $this->getFileType($extension);

            if (!$type) {
                $this->warn("⚠️  Skipping unsupported file: {$file->getFilename()}");
                $skipped++;
                continue;
            }

            try {
                if ($dryRun) {
                    $this->line("➕ Would add: {$file->getFilename()} (Type: {$type})");
                    $added++;
                } else {
                    // إنشاء سجل في قاعدة البيانات
                    MediaItem::create([
                        'file_path' => $relativePath,
                        'file_type' => $type,
                        'duration' => 10, // القيمة الافتراضية
                    ]);
                    
                    $this->info("✅ Added: {$file->getFilename()} (Type: {$type})");
                    $added++;
                }
            } catch (\Exception $e) {
                $this->error("❌ Error adding {$file->getFilename()}: {$e->getMessage()}");
                $errors++;
            }
        }

        // عرض الملخص
        $this->newLine();
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        if ($dryRun) {
            $this->info("📊 Summary (DRY RUN):");
        } else {
            $this->info("📊 Summary:");
        }
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("✅ Added: {$added}");
        $this->info("⏭️  Skipped: {$skipped}");
        if ($errors > 0) {
            $this->error("❌ Errors: {$errors}");
        }
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        return Command::SUCCESS;
    }

    /**
     * تحديد نوع الملف حسب الامتداد
     */
    protected function getFileType(string $extension): ?string
    {
        if (in_array($extension, $this->allowedExtensions['images'])) {
            return 'image';
        }
        
        if (in_array($extension, $this->allowedExtensions['videos'])) {
            return 'video';
        }
        
        if (in_array($extension, $this->allowedExtensions['audio'])) {
            return 'audio';
        }

        return null;
    }
}

