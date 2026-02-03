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

    /**
     * دالة مساعدة لإعادة المحاولة مع معالجة أخطاء DNS
     */
    private function broadcastWithRetry($event, $maxRetries = 3)
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                Log::info("--- BROADCAST: Attempt {$attempt}/{$maxRetries} ---");

                // محاولة البث
                broadcast($event);

                Log::info("--- BROADCAST: Success on attempt {$attempt} ---");
                return true;

            } catch (\Exception $e) {
                $lastException = $e;
                $errorMessage = $e->getMessage();

                Log::warning("--- BROADCAST: Attempt {$attempt} failed: {$errorMessage} ---");

                // إذا كان الخطأ متعلق بـ DNS أو الاتصال، انتظر قليلاً ثم أعد المحاولة
                if (str_contains($errorMessage, 'Could not resolve host') ||
                    str_contains($errorMessage, 'cURL error 6') ||
                    str_contains($errorMessage, 'cURL error 7')) {

                    if ($attempt < $maxRetries) {
                        // انتظر ثانية واحدة قبل إعادة المحاولة
                        sleep(1);
                        continue;
                    }
                } else {
                    // للأخطاء الأخرى، لا نحاول مرة أخرى
                    break;
                }
            }
        }

        // إذا فشلت جميع المحاولات
        throw $lastException;
    }

    public function store(Request $request)
    {
        $request->validate(['media_item_id' => 'required|exists:media_items,id']);
        $mediaItem = MediaItem::find($request->media_item_id);

        try {
            Log::info('--- BROADCAST: Creating new BroadcastMedia event instance. ---');
            $event = new BroadcastMedia($mediaItem);
            Log::info('--- BROADCAST: Calling the broadcast() helper function. ---');

            // استخدام دالة البث مع إعادة المحاولة
            $this->broadcastWithRetry($event);

            Log::info('--- BROADCAST: broadcast() helper function executed without errors. ---');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            Log::error('--- BROADCAST FAILED ---: ' . $errorMessage);

            // رسالة خطأ أكثر وضوحاً للمستخدم
            $userMessage = 'فشل بدء البث';

            if (str_contains($errorMessage, 'Could not resolve host') ||
                str_contains($errorMessage, 'cURL error 6')) {
                $userMessage = 'فشل الاتصال بخادم البث. يرجى التحقق من إعدادات DNS والاتصال بالإنترنت.';
            } elseif (str_contains($errorMessage, 'cURL error 7')) {
                $userMessage = 'فشل الاتصال بخادم البث. يرجى التحقق من الاتصال بالإنترنت وإعدادات الجدار الناري.';
            }

            return back()->with('error', $userMessage);
        }

        Cache::put('active_broadcast_media_id', $mediaItem->id);
        return back()->with('success', 'تم بدء البث العام بنجاح.');
    }

    public function destroy()
    {
        try {
            Log::info('--- BROADCAST: Calling broadcast() for StopBroadcast. ---');

            // استخدام دالة البث مع إعادة المحاولة
            $this->broadcastWithRetry(new StopBroadcast());

            Log::info('--- BROADCAST: StopBroadcast executed without errors. ---');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            Log::error('--- BROADCAST STOP FAILED ---: ' . $errorMessage);

            // رسالة خطأ أكثر وضوحاً للمستخدم
            $userMessage = 'فشل إيقاف البث';

            if (str_contains($errorMessage, 'Could not resolve host') ||
                str_contains($errorMessage, 'cURL error 6')) {
                $userMessage = 'فشل الاتصال بخادم البث. يرجى التحقق من إعدادات DNS والاتصال بالإنترنت.';
            }

            return back()->with('error', $userMessage);
        }

        Cache::forget('active_broadcast_media_id');
        return back()->with('success', 'تم إيقاف البث العام.');
    }
}

