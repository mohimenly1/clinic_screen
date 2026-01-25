<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class VideoOptimizer
{
    /**
     * تحسين الفيديو وإعادة ترميزه إلى صيغة متوافقة مع المتصفحات
     * 
     * @param string $inputPath المسار الكامل للملف الأصلي
     * @param string $outputPath المسار المطلوب للملف المحسّن
     * @return bool|string false في حالة الفشل، أو مسار الملف المحسّن في حالة النجاح
     */
    public function optimize(string $inputPath, string $outputPath): bool|string
    {
        // التحقق من وجود FFmpeg
        if (!$this->isFFmpegAvailable()) {
            Log::warning('FFmpeg is not available, skipping video optimization');
            return false;
        }

        // التحقق من وجود الملف الأصلي
        if (!file_exists($inputPath)) {
            Log::error("Input video file not found: {$inputPath}");
            return false;
        }

        try {
            // إنشاء المجلد إذا لم يكن موجوداً
            $outputDir = dirname($outputPath);
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            // تحديد أمر FFmpeg الصحيح
            $ffmpegCmd = $this->getFFmpegCommand();
            
            Log::info('Starting video optimization', [
                'input' => $inputPath,
                'output' => $outputPath,
                'ffmpeg_command' => $ffmpegCmd,
            ]);

            // بناء أمر FFmpeg لتحسين الفيديو
            // المعاملات المستخدمة:
            // -c:v libx264: استخدام H.264 codec
            // -profile:v baseline: استخدام Baseline Profile (أكثر توافقاً)
            // -level 3.0: مستوى التوافق
            // -pix_fmt yuv420p: تنسيق البكسل المتوافق مع جميع المتصفحات
            // -movflags +faststart: نقل metadata إلى بداية الملف (للتحميل التدريجي)
            // -c:a aac: استخدام AAC للصوت
            // -b:a 128k: معدل البت للصوت
            // -preset medium: توازن بين السرعة والجودة
            // -crf 23: جودة الفيديو (23 = جودة جيدة)
            $command = [
                $ffmpegCmd,
                '-i', $inputPath,
                '-c:v', 'libx264',
                '-profile:v', 'baseline',
                '-level', '3.0',
                '-pix_fmt', 'yuv420p',
                '-movflags', '+faststart',
                '-c:a', 'aac',
                '-b:a', '128k',
                '-preset', 'medium',
                '-crf', '23',
                '-y', // الكتابة فوق الملف إذا كان موجوداً
                $outputPath
            ];

            $process = new Process($command);
            $process->setTimeout(600); // 10 دقائق كحد أقصى
            
            Log::info('Running FFmpeg command', [
                'command' => implode(' ', $command),
                'input_exists' => file_exists($inputPath),
                'input_size' => file_exists($inputPath) ? filesize($inputPath) : 0,
            ]);
            
            $process->run();

            if (!$process->isSuccessful()) {
                $errorOutput = $process->getErrorOutput();
                $standardOutput = $process->getOutput();
                
                Log::error('FFmpeg optimization failed', [
                    'command' => implode(' ', $command),
                    'exit_code' => $process->getExitCode(),
                    'error_output' => $errorOutput,
                    'standard_output' => $standardOutput,
                    'input_exists' => file_exists($inputPath),
                    'input_size' => file_exists($inputPath) ? filesize($inputPath) : 0,
                ]);
                return false;
            }
            
            Log::info('FFmpeg process completed successfully', [
                'exit_code' => $process->getExitCode(),
            ]);

            // التحقق من وجود الملف المحسّن
            if (!file_exists($outputPath)) {
                Log::error("Optimized video file was not created: {$outputPath}");
                return false;
            }

            Log::info('Video optimized successfully', [
                'input' => $inputPath,
                'output' => $outputPath,
                'input_size' => filesize($inputPath),
                'output_size' => filesize($outputPath),
            ]);

            return $outputPath;

        } catch (\Exception $e) {
            Log::error('Video optimization exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * التحقق من وجود FFmpeg في النظام
     * يدعم Windows و macOS و Linux
     */
    public function isFFmpegAvailable(): bool
    {
        try {
            // على Windows، قد يكون FFmpeg في مسارات مختلفة
            $commands = ['ffmpeg', 'ffmpeg.exe'];
            
            // محاولة استخدام exec مباشرة أولاً (أكثر موثوقية)
            foreach ($commands as $cmd) {
                try {
                    $output = [];
                    $returnCode = 0;
                    // استخدام المسار الكامل إذا كان متاحاً
                    $fullCommand = escapeshellcmd($cmd) . ' -version 2>&1';
                    
                    // إضافة PATH إلى البيئة قبل exec (للمتصفحات التي لا تحتوي على PATH)
                    $originalPath = getenv('PATH');
                    if (empty($originalPath)) {
                        putenv('PATH=/usr/local/bin:/usr/bin:/bin:/opt/homebrew/bin');
                    }
                    
                    exec($fullCommand, $output, $returnCode);
                    
                    // استعادة PATH الأصلي
                    if (empty($originalPath)) {
                        putenv('PATH=');
                    }
                    
                    if ($returnCode === 0 && !empty($output) && str_contains(implode(' ', $output), 'ffmpeg version')) {
                        Log::info('FFmpeg found via exec', [
                            'command' => $cmd,
                            'version_line' => $output[0] ?? 'N/A',
                        ]);
                        return true;
                    }
                } catch (\Exception $e) {
                    Log::debug('FFmpeg exec check failed', [
                        'command' => $cmd,
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }
            }
            
            // إذا فشل exec، جرب Process مع PATH صريح
            foreach ($commands as $cmd) {
                try {
                    $process = new Process([$cmd, '-version']);
                    $process->setTimeout(10);
                    // إضافة PATH من البيئة الحالية
                    $currentPath = getenv('PATH');
                    if (empty($currentPath)) {
                        // قيم افتراضية شائعة على macOS
                        $currentPath = '/usr/local/bin:/usr/bin:/bin:/opt/homebrew/bin';
                    }
                    $process->setEnv(['PATH' => $currentPath]);
                    $process->run();
                    
                    $output = trim($process->getOutput());
                    if ($process->isSuccessful() && !empty($output) && str_contains($output, 'ffmpeg version')) {
                        Log::info('FFmpeg found via Process', [
                            'command' => $cmd,
                            'version_output' => substr($output, 0, 100),
                        ]);
                        return true;
                    }
                } catch (\Exception $e) {
                    Log::debug('FFmpeg Process check failed', [
                        'command' => $cmd,
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }
            }
            
            // محاولة أخيرة: استخدام which/where للعثور على FFmpeg
            $whichCommand = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where' : 'which';
            try {
                $whichProcess = new Process([$whichCommand, 'ffmpeg']);
                $whichProcess->setTimeout(5);
                $whichProcess->run();
                
                if ($whichProcess->isSuccessful()) {
                    $ffmpegPath = trim($whichProcess->getOutput());
                    if (!empty($ffmpegPath) && file_exists($ffmpegPath)) {
                        Log::info('FFmpeg found via which/where', [
                            'path' => $ffmpegPath,
                        ]);
                        return true;
                    }
                }
            } catch (\Exception $e) {
                // تجاهل الخطأ
            }
            
            Log::warning('FFmpeg not found in system', [
                'tried_commands' => $commands,
                'php_os' => PHP_OS,
                'path_env' => getenv('PATH'),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('FFmpeg check exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * الحصول على أمر FFmpeg الصحيح حسب النظام
     */
    private function getFFmpegCommand(): string
    {
        // على Windows، جرب .exe أولاً
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $commands = ['ffmpeg.exe', 'ffmpeg'];
            foreach ($commands as $cmd) {
                try {
                    $process = new Process([$cmd, '-version']);
                    $process->setTimeout(5);
                    $process->run();
                    if ($process->isSuccessful()) {
                        return $cmd;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        
        return 'ffmpeg';
    }

    /**
     * الحصول على مدة الفيديو بالثواني
     */
    public function getDuration(string $videoPath): ?int
    {
        if (!$this->isFFmpegAvailable() || !file_exists($videoPath)) {
            return null;
        }

        try {
            // تحديد أمر ffprobe حسب النظام
            $ffprobeCmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'ffprobe.exe' : 'ffprobe';
            
            $command = [
                $ffprobeCmd,
                '-v', 'error',
                '-show_entries', 'format=duration',
                '-of', 'default=noprint_wrappers=1:nokey=1',
                $videoPath
            ];

            $process = new Process($command);
            $process->run();

            if ($process->isSuccessful()) {
                $duration = trim($process->getOutput());
                return (int) round((float) $duration);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get video duration', [
                'path' => $videoPath,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
