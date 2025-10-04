<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->string('file_path'); // مسار تخزين الملف
            $table->enum('file_type', ['image', 'video']); // نوع الملف
            $table->integer('duration')->default(10); // مدة عرض الصورة بالثواني (الفيديو لا يحتاجها)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_items');
    }
};