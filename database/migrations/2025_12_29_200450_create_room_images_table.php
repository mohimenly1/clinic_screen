<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('room_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->string('image_path'); // مسار الصورة
            $table->integer('display_order')->default(0); // ترتيب العرض (للوصول التدريجي)
            $table->text('description')->nullable(); // وصف الصورة (يظهر على الصورة)
            $table->text('ar_instructions')->nullable(); // تعليمات الواقع المعزز (مثل "اتجه يميناً")
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // فهرس للترتيب
            $table->index(['room_id', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_images');
    }
};
