<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الطابق مثل "الطابق الأول" أو "الطابق الأرضي"
            $table->integer('floor_number')->unique(); // رقم الطابق (0 للأرضي، 1 للأول، -1 للبدروم)
            $table->string('map_image_path')->nullable(); // مسار صورة خريطة الطابق
            $table->text('description')->nullable(); // وصف الطابق (اختياري)
            $table->integer('display_order')->default(0); // ترتيب العرض
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floors');
    }
};
