<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('navigation_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('to_room_id')->constrained('rooms')->onDelete('cascade');
            $table->text('directions')->nullable(); // تعليمات التنقل النصية
            $table->json('path_coordinates')->nullable(); // إحداثيات المسار (للاستخدام المستقبلي)
            $table->integer('estimated_time_seconds')->nullable(); // الوقت المتوقع للوصول بالثواني
            $table->integer('distance_meters')->nullable(); // المسافة بالمتر (اختياري)
            $table->timestamps();
            
            // فهرس مركب لتجنب المسارات المكررة
            $table->unique(['from_room_id', 'to_room_id']);
            $table->index('from_room_id');
            $table->index('to_room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_paths');
    }
};
