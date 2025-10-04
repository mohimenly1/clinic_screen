<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('screens', function (Blueprint $table) {
            // إضافة حقل لربط الشاشة بملف صوتي من مكتبة الوسائط
            $table->foreignId('background_audio_id')
                  ->nullable()
                  ->after('is_active')
                  ->constrained('media_items')
                  ->onDelete('set null'); // إذا تم حذف الملف الصوتي، يصبح الحقل فارغًا
        });
    }

    public function down(): void
    {
        Schema::table('screens', function (Blueprint $table) {
            $table->dropForeign(['background_audio_id']);
            $table->dropColumn('background_audio_id');
        });
    }
};
